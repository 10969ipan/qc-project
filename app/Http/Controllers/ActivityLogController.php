<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Item;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        if ($request->filled('search')) {
            $searchString = strtolower(trim($request->search));
            
            // Smart NLP Search: filter out common stop words
            $stopWords = ['di', 'ke', 'dari', 'yang', 'dan', 'atau', 'untuk', 'dengan', 'pada', 'adalah', 'untuk'];
            $terms = explode(' ', $searchString);
            $validTerms = array_filter($terms, function($term) use ($stopWords) {
                return !in_array($term, $stopWords) && strlen($term) > 1;
            });
            
            if (empty($validTerms)) {
                $validTerms = [$searchString];
            }

            foreach ($validTerms as $term) {
                $query->where(function($q) use ($term) {
                    $q->where('description', 'like', "%{$term}%")
                      ->orWhere('action', 'like', "%{$term}%")
                      ->orWhereHas('user', function($u) use ($term) {
                          $u->where('name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%");
                      });
                });
            }
        }

        $logs = $query->paginate(10);

        return response()->json($logs);
    }

    public function getItemLogs(Request $request, $itemId)
    {
        $paginator = ActivityLog::with('user:id,name')
            ->where('model_type', Item::class)
            ->where('model_id', $itemId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $paginator->getCollection()->transform(function ($log) {
            return [
                'id' => $log->id,
                'action' => $log->action,
                'description' => $log->description,
                'user' => $log->user ? $log->user->name : 'System',
                'date' => Carbon::parse($log->created_at)->translatedFormat('d M Y, H:i'),
            ];
        });

        return response()->json([
            'success' => true,
            'logs' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ]
        ]);
    }
}
