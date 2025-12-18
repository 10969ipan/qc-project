import re
import os
from playwright.sync_api import sync_playwright

def run(playwright):
    browser = playwright.chromium.launch(headless=True)
    page = browser.new_page()
    cwd = os.getcwd()
    file_path = f"file://{cwd}/verify_timer.html"
    print(f"Navigating to {file_path}")
    page.goto(file_path)

    # Check initial state
    initial_timer = page.text_content("#timerDisplay")
    print(f"Initial Timer: {initial_timer}")
    assert initial_timer == "00:00:00"
    
    initial_input = page.input_value("#cycleTimeInput")
    print(f"Initial Input: {initial_input}")
    assert initial_input == "0"

    # Click Start
    page.click("#startTimerBtn")
    print("Clicked Start")

    # Wait for 3 seconds
    page.wait_for_timeout(3100) 

    # Check timer (should be around 00:00:03)
    current_timer = page.text_content("#timerDisplay")
    print(f"Current Timer: {current_timer}")
    assert "00:00:03" in current_timer or "00:00:04" in current_timer or "00:00:02" in current_timer

    current_input = page.input_value("#cycleTimeInput")
    print(f"Current Input: {current_input}")
    assert int(current_input) >= 2

    # Click Submit (Simpan Data)
    page.click("#submitBtn")
    print("Clicked Submit")

    # Check result div
    result_text = page.text_content("#result")
    print(f"Result: {result_text}")
    assert "Submitted:" in result_text
    
    # Extract value
    match = re.search(r"Submitted: (\d+)", result_text)
    if match:
        final_val = int(match.group(1))
        print(f"Final Value: {final_val}")
        assert final_val >= 3
    else:
        print("Could not find submitted value")
        exit(1)

    browser.close()

with sync_playwright() as playwright:
    run(playwright)
