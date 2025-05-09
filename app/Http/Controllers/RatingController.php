<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Modules\Book\Models\Book;
use App\Services\RatingService;
use App\Helpers\Debug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class RatingController extends Controller
{
    protected $ratingService;

    /**
     * Khởi tạo controller và áp dụng middleware
     */
    public function __construct(RatingService $ratingService)
    {
        $this->ratingService = $ratingService;
        $this->middleware('auth')->except(['index']);
    }

    /**
     * Hiển thị tất cả các đánh giá của một cuốn sách
     */
    public function index(Request $request, $bookId)
    {
        try {
            Debug::info("Đang tải đánh giá cho sách ID: {$bookId}");
            $book = Book::findOrFail($bookId);
            $ratings = $this->ratingService->getBookRatings($bookId);
            $stats = $this->ratingService->getRatingStats($bookId);
            
            if ($request->ajax()) {
                return response()->json([
                    'ratings' => $ratings,
                    'stats' => $stats
                ]);
            }
            
            return view('frontend.ratings.index', compact('book', 'ratings', 'stats'));
        } catch (\Exception $e) {
            Debug::error("Lỗi khi tải đánh giá: " . $e->getMessage(), [
                'book_id' => $bookId,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'error' => 'Có lỗi xảy ra khi tải đánh giá', 
                    'details' => env('APP_DEBUG', false) ? $e->getMessage() : null
                ], 500);
            }
            
            abort(500, 'Có lỗi xảy ra khi tải đánh giá');
        }
    }

    /**
     * Lưu đánh giá mới
     */
    public function store(Request $request, $bookId)
    {
        try {
            Debug::info("Đang lưu đánh giá mới cho sách ID: {$bookId}");
            
            $validator = Validator::make($request->all(), [
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                Debug::warning("Lỗi validation khi tạo đánh giá", [
                    'book_id' => $bookId,
                    'errors' => $validator->errors()->toArray()
                ]);
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $userId = Auth::id();
            $rating = $this->ratingService->createOrUpdateRating(
                $bookId,
                $userId,
                $request->rating,
                $request->comment
            );

            Debug::info("Đã lưu đánh giá thành công", [
                'book_id' => $bookId,
                'rating_id' => $rating->id
            ]);
            
            return response()->json(['rating' => $rating], 201);
        } catch (\Exception $e) {
            Debug::error("Lỗi khi lưu đánh giá: " . $e->getMessage(), [
                'book_id' => $bookId,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'error' => 'Có lỗi xảy ra khi gửi đánh giá', 
                'details' => env('APP_DEBUG', false) ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Cập nhật đánh giá hiện tại
     */
    public function update(Request $request, $bookId)
    {
        try {
            Debug::info("Đang cập nhật đánh giá cho sách ID: {$bookId}");
            
            $validator = Validator::make($request->all(), [
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                Debug::warning("Lỗi validation khi cập nhật đánh giá", [
                    'book_id' => $bookId,
                    'errors' => $validator->errors()->toArray()
                ]);
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $userId = Auth::id();
            $rating = $this->ratingService->createOrUpdateRating(
                $bookId,
                $userId,
                $request->rating,
                $request->comment
            );

            Debug::info("Đã cập nhật đánh giá thành công", [
                'book_id' => $bookId,
                'rating_id' => $rating->id
            ]);
            
            return response()->json(['rating' => $rating], 200);
        } catch (\Exception $e) {
            Debug::error("Lỗi khi cập nhật đánh giá: " . $e->getMessage(), [
                'book_id' => $bookId,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'error' => 'Có lỗi xảy ra khi cập nhật đánh giá', 
                'details' => env('APP_DEBUG', false) ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Xóa đánh giá
     */
    public function destroy($ratingId)
    {
        try {
            Debug::info("Đang xóa đánh giá ID: {$ratingId}");
            
            $result = $this->ratingService->deleteRating($ratingId);
            
            if (!$result) {
                Debug::warning("Không có quyền xóa đánh giá", [
                    'rating_id' => $ratingId,
                    'user_id' => Auth::id()
                ]);
                return response()->json(['error' => 'Bạn không có quyền xóa đánh giá này'], 403);
            }
            
            Debug::info("Đã xóa đánh giá thành công", ['rating_id' => $ratingId]);
            
            return response()->json(['message' => 'Đánh giá đã được xóa thành công']);
        } catch (\Exception $e) {
            Debug::error("Lỗi khi xóa đánh giá: " . $e->getMessage(), [
                'rating_id' => $ratingId,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'error' => 'Có lỗi xảy ra khi xóa đánh giá', 
                'details' => env('APP_DEBUG', false) ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Lấy đánh giá của người dùng hiện tại cho một cuốn sách
     */
    public function show(Request $request, $bookId)
    {
        try {
            Debug::info("Đang tải đánh giá của người dùng cho sách ID: {$bookId}");
            
            if (!Auth::check()) {
                Debug::warning("Cố gắng tải đánh giá khi chưa đăng nhập", ['book_id' => $bookId]);
                return response()->json(['error' => 'Bạn cần đăng nhập để xem đánh giá của mình'], 401);
            }
            
            $rating = $this->ratingService->getUserRating($bookId);
            
            if (!$rating) {
                Debug::info("Không tìm thấy đánh giá của người dùng", [
                    'book_id' => $bookId,
                    'user_id' => Auth::id()
                ]);
                return response()->json(['rating' => null], 404);
            }
            
            Debug::info("Đã tải đánh giá người dùng thành công", [
                'book_id' => $bookId,
                'rating_id' => $rating->id
            ]);
            
            return response()->json(['rating' => $rating]);
        } catch (\Exception $e) {
            Debug::error("Lỗi khi tải đánh giá người dùng: " . $e->getMessage(), [
                'book_id' => $bookId,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'error' => 'Có lỗi xảy ra khi lấy đánh giá của bạn', 
                'details' => env('APP_DEBUG', false) ? $e->getMessage() : null
            ], 500);
        }
    }
} 