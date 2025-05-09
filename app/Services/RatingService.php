<?php

namespace App\Services;

use App\Models\Rating;
use App\Modules\Book\Models\Book;
use App\Helpers\Debug;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RatingService
{
    /**
     * Thêm hoặc cập nhật đánh giá cho sách
     *
     * @param int $bookId
     * @param int $userId
     * @param float $rating
     * @param string|null $comment
     * @return Rating
     */
    public function createOrUpdateRating($bookId, $userId, $rating, $comment = null)
    {
        Debug::info("Đang tạo/cập nhật đánh giá", [
            'book_id' => $bookId,
            'user_id' => $userId,
            'rating' => $rating
        ]);
        
        // Kiểm tra xem người dùng đã đánh giá sách này chưa
        $existingRating = Rating::where('book_id', $bookId)
            ->where('user_id', $userId)
            ->first();
            
        if ($existingRating) {
            Debug::info("Cập nhật đánh giá hiện có", ['rating_id' => $existingRating->id]);
            
            // Cập nhật đánh giá hiện có
            $existingRating->rating = $rating;
            if ($comment !== null) {
                $existingRating->comment = $comment;
            }
            $existingRating->save();
            
            // Cập nhật trung bình đánh giá của sách
            $this->updateBookAverageRating($bookId);
            
            return $existingRating;
        } else {
            Debug::info("Tạo đánh giá mới");
            
            // Tạo đánh giá mới
            $newRating = Rating::create([
                'book_id' => $bookId,
                'user_id' => $userId,
                'rating' => $rating,
                'comment' => $comment,
            ]);
            
            Debug::info("Đã tạo đánh giá mới", ['rating_id' => $newRating->id]);
            
            // Cập nhật trung bình đánh giá của sách
            $this->updateBookAverageRating($bookId);
            
            return $newRating;
        }
    }
    
    /**
     * Xóa đánh giá 
     *
     * @param int $ratingId
     * @return bool
     */
    public function deleteRating($ratingId)
    {
        try {
            Debug::info("Đang xóa đánh giá", ['rating_id' => $ratingId]);
            
            $rating = Rating::findOrFail($ratingId);
            $bookId = $rating->book_id;
            
            // Kiểm tra quyền xóa (người dùng là người đánh giá hoặc admin)
            $user = Auth::user();
            if (Auth::id() === $rating->user_id || ($user && $user->id == 1)) {
                $rating->delete();
                
                Debug::info("Đánh giá đã xóa thành công", [
                    'rating_id' => $ratingId,
                    'book_id' => $bookId
                ]);
                
                // Cập nhật trung bình đánh giá của sách
                $this->updateBookAverageRating($bookId);
                
                return true;
            }
            
            Debug::warning("Không đủ quyền xóa đánh giá", [
                'rating_id' => $ratingId,
                'user_id' => Auth::id(),
                'rating_user_id' => $rating->user_id
            ]);
            
            return false;
        } catch (\Exception $e) {
            Debug::error("Lỗi khi xóa đánh giá: " . $e->getMessage(), [
                'rating_id' => $ratingId,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Cập nhật trung bình đánh giá của sách
     *
     * @param int $bookId
     * @return void
     */
    public function updateBookAverageRating($bookId)
    {
        try {
            Debug::info("Đang cập nhật trung bình đánh giá cho sách", ['book_id' => $bookId]);
            
            $book = Book::findOrFail($bookId);
            
            $averageRating = Rating::where('book_id', $bookId)->avg('rating') ?? 0;
            $ratingCount = Rating::where('book_id', $bookId)->count();
            
            $book->average_rating = $averageRating;
            $book->rating_count = $ratingCount;
            $book->save();
            
            Debug::info("Đã cập nhật trung bình đánh giá", [
                'book_id' => $bookId,
                'average' => $averageRating,
                'count' => $ratingCount
            ]);
        } catch (\Exception $e) {
            Debug::error("Lỗi khi cập nhật trung bình đánh giá: " . $e->getMessage(), [
                'book_id' => $bookId,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Lấy đánh giá của người dùng hiện tại cho sách
     *
     * @param int $bookId
     * @return Rating|null
     */
    public function getUserRating($bookId)
    {
        try {
            if (!Auth::check()) {
                Debug::info("Người dùng chưa đăng nhập khi lấy đánh giá");
                return null;
            }
            
            $userId = Auth::id();
            Debug::info("Đang tìm đánh giá của người dùng", [
                'book_id' => $bookId,
                'user_id' => $userId
            ]);
            
            $rating = Rating::where('book_id', $bookId)
                ->where('user_id', $userId)
                ->first();
            
            if ($rating) {
                Debug::info("Đã tìm thấy đánh giá của người dùng", ['rating_id' => $rating->id]);
            } else {
                Debug::info("Không tìm thấy đánh giá của người dùng");
            }
            
            return $rating;
        } catch (\Exception $e) {
            Debug::error("Lỗi khi lấy đánh giá của người dùng: " . $e->getMessage(), [
                'book_id' => $bookId,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return null;
        }
    }
    
    /**
     * Lấy tất cả đánh giá cho sách
     *
     * @param int $bookId
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getBookRatings($bookId, $perPage = 10)
    {
        try {
            Debug::info("Đang tải danh sách đánh giá cho sách", [
                'book_id' => $bookId,
                'per_page' => $perPage
            ]);
            
            $ratings = Rating::with('user')
                ->where('book_id', $bookId)
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
            
            Debug::info("Đã tải đánh giá thành công", [
                'book_id' => $bookId,
                'count' => $ratings->count(),
                'total' => $ratings->total()
            ]);
            
            return $ratings;
        } catch (\Exception $e) {
            Debug::error("Lỗi khi tải danh sách đánh giá: " . $e->getMessage(), [
                'book_id' => $bookId,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Lấy thông tin thống kê đánh giá của sách
     *
     * @param int $bookId
     * @return array
     */
    public function getRatingStats($bookId)
    {
        try {
            Debug::info("Đang lấy thống kê đánh giá cho sách", ['book_id' => $bookId]);
            
            $stats = [
                'average' => 0,
                'count' => 0,
                'distribution' => [
                    5 => 0,
                    4 => 0,
                    3 => 0,
                    2 => 0, 
                    1 => 0
                ]
            ];
            
            $ratings = Rating::where('book_id', $bookId)->get();
            $stats['count'] = $ratings->count();
            
            if ($stats['count'] > 0) {
                $stats['average'] = $ratings->avg('rating');
                
                // Tính phân bố đánh giá
                foreach ($ratings as $rating) {
                    $ratingValue = floor($rating->rating);
                    $stats['distribution'][$ratingValue]++;
                }
            }
            
            Debug::info("Đã lấy thống kê đánh giá thành công", [
                'book_id' => $bookId, 
                'stats' => $stats
            ]);
            
            return $stats;
        } catch (\Exception $e) {
            Debug::error("Lỗi khi lấy thống kê đánh giá: " . $e->getMessage(), [
                'book_id' => $bookId,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return [
                'average' => 0,
                'count' => 0,
                'distribution' => [
                    5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0
                ]
            ];
        }
    }
} 