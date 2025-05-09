<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Modules\Book\Models\BookType;
use App\Modules\Book\Models\Book;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    protected $pagesize;
    
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->pagesize = env('NUMBER_PER_PAGE','20');
        $this->middleware('admin.auth');
    }
    
    public function index()
    {
        //
       
        $func = "admin_view";
        if(!$this->check_function($func))
        {
            return redirect()->route('home');
        }
        $data['breadcrumb'] = '
        <li class="breadcrumb-item"><a href="#">/</a></li>
        <li class="breadcrumb-item active" aria-current="page"> Bảng điều khiển</li>';
       $data['active_menu']='dashboard';
        return view ('backend.index',   $data);
   
        // echo 'i am admin';
    }
    
    /**
     * API endpoint to get book count by category
     */
    public function booksByCategory()
    {
        try {
            // Kiểm tra xem model có tồn tại không
            if (!class_exists(BookType::class) || !class_exists(Book::class)) {
                return response()->json([
                    ['title' => 'Không có dữ liệu', 'count' => 0]
                ]);
            }
            
            // Lấy danh sách BookType có sách
            $bookTypes = BookType::where('status', 'active')
                ->withCount(['books' => function($query) {
                    $query->where('status', 'active');
                }])
                ->having('books_count', '>', 0)
                ->orderBy('books_count', 'desc')
                ->limit(10)
                ->get(['id', 'title']);
            
            // Chuyển đổi dữ liệu để trả về
            $result = $bookTypes->map(function($type) {
                return [
                    'title' => $type->title,
                    'count' => $type->books_count
                ];
            });
            
            // Nếu không có dữ liệu, trả về dữ liệu mặc định
            if ($result->isEmpty()) {
                return response()->json([
                    ['title' => 'Không có dữ liệu', 'count' => 0]
                ]);
            }
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                ['title' => 'Lỗi dữ liệu', 'count' => 0]
            ]);
        }
    }
    
    /**
     * API endpoint to get user distribution by points
     */
    public function usersByPoints()
    {
        try {
            // Kiểm tra xem model có tồn tại không
            if (!class_exists(User::class)) {
                return response()->json([0, 0, 0, 0, 0, 0]);
            }
            
            // Đảm bảo trường totalpoint tồn tại trong bảng users
            if (!Schema::hasColumn('users', 'totalpoint')) {
                return response()->json([0, 0, 0, 0, 0, 0]);
            }
            
            $ranges = [
                DB::raw('COUNT(CASE WHEN totalpoint BETWEEN 0 AND 100 THEN 1 END) as range1'),
                DB::raw('COUNT(CASE WHEN totalpoint BETWEEN 101 AND 500 THEN 1 END) as range2'),
                DB::raw('COUNT(CASE WHEN totalpoint BETWEEN 501 AND 1000 THEN 1 END) as range3'),
                DB::raw('COUNT(CASE WHEN totalpoint BETWEEN 1001 AND 2000 THEN 1 END) as range4'),
                DB::raw('COUNT(CASE WHEN totalpoint BETWEEN 2001 AND 5000 THEN 1 END) as range5'),
                DB::raw('COUNT(CASE WHEN totalpoint > 5000 THEN 1 END) as range6')
            ];
            
            $result = User::where('status', 'active')
                ->select($ranges)
                ->first();
                
            if (!$result) {
                return response()->json([0, 0, 0, 0, 0, 0]);
            }
            
            $data = [
                (int)($result->range1 ?? 0),
                (int)($result->range2 ?? 0),
                (int)($result->range3 ?? 0),
                (int)($result->range4 ?? 0),
                (int)($result->range5 ?? 0),
                (int)($result->range6 ?? 0)
            ];
            
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([0, 0, 0, 0, 0, 0]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
