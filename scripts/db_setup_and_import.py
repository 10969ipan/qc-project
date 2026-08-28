import sqlite3
import re
import datetime
import os

# Database path
db_path = 'database/database.sqlite'

# NOTE: This script is a workaround because the PHP environment is not available
# to run standard Laravel migrations and seeders.
# It manually creates the schema and imports data from a text dump.

# Connect to database
conn = sqlite3.connect(db_path)
cursor = conn.cursor()

# 1. Initialize Tables (Schema)
# ==============================

# Create items table
cursor.execute('''
CREATE TABLE IF NOT EXISTS items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR NOT NULL,
    file_path VARCHAR NULL,
    customer TEXT NULL,
    part_number VARCHAR NULL,
    defects JSON NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
''')

# Create checksheets table
cursor.execute('''
CREATE TABLE IF NOT EXISTS checksheets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    item_id INTEGER NOT NULL,
    date DATE NOT NULL,
    shift VARCHAR NOT NULL,
    total_qty INTEGER NOT NULL,
    sampling_qty INTEGER NOT NULL,
    total_ok INTEGER NOT NULL,
    total_ng INTEGER NOT NULL,
    judgment VARCHAR NOT NULL,
    operator_initials VARCHAR NULL,
    kashift_qc VARCHAR NULL,
    kashift_approved_at TIMESTAMP NULL,
    supervisor_qc VARCHAR NULL,
    supervisor_approved_at TIMESTAMP NULL,
    asst_manager_qc VARCHAR NULL,
    asst_manager_approved_at TIMESTAMP NULL,
    approval_status VARCHAR NULL,
    remarks TEXT NULL,
    defects JSON NULL,
    cycle_time INTEGER NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY(item_id) REFERENCES items(id) ON DELETE CASCADE
);
''')

# Create users table
cursor.execute('''
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR NOT NULL,
    email VARCHAR NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR NOT NULL,
    remember_token VARCHAR NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    role VARCHAR NOT NULL DEFAULT 'inspector'
);
''')

conn.commit()
print("Tables initialized.")

# 2. Parse and Import Data
# ========================

def parse_record(line_content):
    # Remove newlines and extra spaces
    line = re.sub(r'\s+', ' ', line_content).strip()

    # Check if it has "PT. ASTRA HONDA MOTOR"
    if "PT. ASTRA HONDA MOTOR" not in line:
        return None

    parts = line.split("PT. ASTRA HONDA MOTOR")
    left_part = parts[0].strip()
    right_part = parts[1].strip()
    customer = "PT. ASTRA HONDA MOTOR"

    tokens = left_part.split()

    # Check for date (YYYY-MM-DD)
    date_index = -1
    for i, token in enumerate(tokens):
        if re.match(r'\d{4}-\d{2}-\d{2}', token):
            date_index = i
            break

    if date_index == -1:
        return None

    date_str = tokens[date_index]

    # After date, we expect times and cycle time
    times = []
    current_idx = date_index + 1
    while current_idx < len(tokens) and re.match(r'\d{1,2}:\d{2}:\d{2}', tokens[current_idx]):
        times.append(tokens[current_idx])
        current_idx += 1

    # Next should be Cycle Time (int)
    if current_idx < len(tokens) and tokens[current_idx].isdigit():
        cycle_time = int(tokens[current_idx])
        current_idx += 1
    else:
        cycle_time = 0

    # Next should be Shift (int)
    if current_idx < len(tokens) and tokens[current_idx].isdigit():
        shift = tokens[current_idx]
        current_idx += 1
    else:
        shift = "1"

    name_part_tokens = tokens[current_idx:]

    name_tokens = []
    part_tokens = []

    found_part = False
    for t in name_part_tokens:
        if (re.match(r'^\d{4,}', t) and not t.isdigit()) or '-' in t:
             found_part = True

        if found_part:
            part_tokens.append(t)
        else:
            name_tokens.append(t)

    name = " ".join(name_tokens).replace(",", ", ").strip()
    name = re.sub(r'\s+', ' ', name)

    part_number = " ".join(part_tokens)

    r_tokens = right_part.split()
    if len(r_tokens) < 5:
        return None

    try:
        total_qty = int(r_tokens[0])
        sampling_qty = int(r_tokens[1])
        total_ok = int(r_tokens[2])
        total_ng = int(r_tokens[3])
        judgment = r_tokens[4]
        if len(r_tokens) > 5:
            operator = r_tokens[5]
            remarks = " ".join(r_tokens[6:]) if len(r_tokens) > 6 else "-"
        else:
            operator = "-"
            remarks = "-"

    except ValueError:
        return None

    time_str = times[-1] if times else "00:00:00"
    created_at = f"{date_str} {time_str}"

    return {
        'date': date_str,
        'created_at': created_at,
        'cycle_time': cycle_time,
        'shift': shift,
        'item_name': name,
        'part_number': part_number,
        'customer': customer,
        'total_qty': total_qty,
        'sampling_qty': sampling_qty,
        'total_ok': total_ok,
        'total_ng': total_ng,
        'judgment': judgment,
        'operator': operator,
        'remarks': remarks
    }

# Read dump file
# Ideally this should be passed as an argument or read from a temporary location
dump_file = 'dump.txt'
if not os.path.exists(dump_file):
    print(f"Error: {dump_file} not found. Please place the data dump in {dump_file}")
    exit(1)

with open(dump_file, 'r') as f:
    lines = f.readlines()

count = 0

# Clean and merge lines
merged_lines = []
buffer = ""

# The dump has a number on its own line before the record?
# Example:
# 2
# 5 2025...
# 3
# 8 2025...

# Strategy: Detect start of a record.
# A record starts with a number on a line by itself (Row Number from sheet?)
# OR it starts with "ID Date..."
# Let's accumulate until we see a line that looks like the start of a new record (Single number or ID+Date)

current_block = ""
for line in lines:
    line = line.strip()
    if not line:
        continue

    # Heuristic: If line is just a number (1-3 digits), it's likely the row number from column A (which was "Item ID" header but acts as row count)
    # But wait, the previous dump showed "Item ID" column containing "5", "8", "9".
    # And then Row number 2, 3...

    # In the dump text:
    # 2
    # 5 2025-12-19 ...
    # This "2" is the spreadsheet row number (visual). "5" is the content of col A.

    # So if we see a line that is just a number, it's likely a separator/row index.
    # We should treat it as a delimiter.

    if line.isdigit() and len(line) < 4:
        # New record start. Process previous block.
        if current_block:
            data = parse_record(current_block)
            if data:
                # Insert logic here
                cursor.execute("SELECT id FROM items WHERE part_number = ?", (data['part_number'],))
                row = cursor.fetchone()

                if row:
                    item_id = row[0]
                else:
                    now = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                    cursor.execute("INSERT INTO items (name, part_number, customer, created_at, updated_at) VALUES (?, ?, ?, ?, ?)",
                                   (data['item_name'], data['part_number'], data['customer'], now, now))
                    item_id = cursor.lastrowid
                    print(f"Created Item: {data['item_name']} ({data['part_number']})")

                # Check duplicate
                cursor.execute("SELECT id FROM checksheets WHERE item_id = ? AND created_at = ?", (item_id, data['created_at']))
                if not cursor.fetchone():
                    now = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                    cursor.execute('''
                        INSERT INTO checksheets (
                            item_id, date, shift, total_qty, sampling_qty, total_ok, total_ng,
                            judgment, operator_initials, remarks, cycle_time, created_at, updated_at, approval_status
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                    ''', (
                        item_id, data['date'], data['shift'], data['total_qty'], data['sampling_qty'],
                        data['total_ok'], data['total_ng'], data['judgment'], data['operator'],
                        data['remarks'], data['cycle_time'], data['created_at'], now, 'Pending'
                    ))
                    count += 1

        current_block = "" # Reset block
    else:
        current_block += " " + line

# Process last block
if current_block:
    data = parse_record(current_block)
    if data:
         # Insert logic here (duplicate of above, sorry for non-DRY in quick script)
        cursor.execute("SELECT id FROM items WHERE part_number = ?", (data['part_number'],))
        row = cursor.fetchone()

        if row:
            item_id = row[0]
        else:
            now = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            cursor.execute("INSERT INTO items (name, part_number, customer, created_at, updated_at) VALUES (?, ?, ?, ?, ?)",
                           (data['item_name'], data['part_number'], data['customer'], now, now))
            item_id = cursor.lastrowid
            print(f"Created Item: {data['item_name']} ({data['part_number']})")

        cursor.execute("SELECT id FROM checksheets WHERE item_id = ? AND created_at = ?", (item_id, data['created_at']))
        if not cursor.fetchone():
            now = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            cursor.execute('''
                INSERT INTO checksheets (
                    item_id, date, shift, total_qty, sampling_qty, total_ok, total_ng,
                    judgment, operator_initials, remarks, cycle_time, created_at, updated_at, approval_status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ''', (
                item_id, data['date'], data['shift'], data['total_qty'], data['sampling_qty'],
                data['total_ok'], data['total_ng'], data['judgment'], data['operator'],
                data['remarks'], data['cycle_time'], data['created_at'], now, 'Pending'
            ))
            count += 1

conn.commit()
print(f"Imported {count} records.")
conn.close()
