<?php

namespace App\Providers;

use App\Models\UserRecentBook;
use App\Modules\Book\Models\Book;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class RecentBooksService
{
    const COOKIE_NAME = 'recent_books';
    const MAX_BOOKS = 20;
    const COOKIE_DURATION = 43200; // 30 ngày

    public function addBook($bookId)
    {
        try {
            if (Auth::check()) {
                $this->addBookForUser(Auth::id(), $bookId);
            } else {
                $this->addBookToCookie($bookId);
            }
            Log::info('Book added to recent', ['book_id' => $bookId]);
        } catch (\Exception $e) {
            Log::error('Error adding book to recent: ' . $e->getMessage());
        }
    }

    private function addBookToCookie($bookId)
    {
        try {
            $recentBooks = json_decode(Cookie::get(self::COOKIE_NAME, '[]'), true);
            Log::info('Current cookie value', ['books' => $recentBooks]);

            // Xóa nếu đã tồn tại và thêm vào đầu mảng
            $recentBooks = array_values(array_diff($recentBooks, [$bookId]));
            array_unshift($recentBooks, $bookId);

            // Giới hạn số lượng
            $recentBooks = array_slice($recentBooks, 0, self::MAX_BOOKS);

            Cookie::queue(self::COOKIE_NAME, json_encode($recentBooks), self::COOKIE_DURATION);
            Log::info('New cookie value', ['books' => $recentBooks]);
        } catch (\Exception $e) {
            Log::error('Error in addBookToCookie: ' . $e->getMessage());
        }
    }

    public function getAllRecentBooks($perPage = 20)
    {
        $books = Auth::check()
            ? $this->getUserRecentBooks(Auth::id())
            : $this->getCookieRecentBooks();

        $page = request()->get('page', 1);
        $paginated = new LengthAwarePaginator(
            $books->forPage($page, $perPage)->values(),
            $books->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return $paginated;
    }

    public function getRecentBooks()
    {
        try {
            if (Auth::check()) {
                $books = $this->getUserRecentBooks(Auth::id());
            } else {
                $books = $this->getCookieRecentBooks();
            }
            Log::info('Retrieved recent books', ['count' => $books->count()]);
            return $books;
        } catch (\Exception $e) {
            Log::error('Error getting recent books: ' . $e->getMessage());
            return collect([]);
        }
    }

    private function getUserRecentBooks($userId)
    {
        try {
            $recentBookIds = UserRecentBook::where('user_id', $userId)
                ->orderBy('read_at', 'desc')
                ->limit(self::MAX_BOOKS)
                ->pluck('book_id');

            return Book::whereIn('id', $recentBookIds)
                ->where('status', 'active')
                ->where('block', 'no')
                ->get()
                ->sortBy(function ($book) use ($recentBookIds) {
                    return array_search($book->id, $recentBookIds->toArray());
                });
        } catch (\Exception $e) {
            Log::error('Error in getUserRecentBooks: ' . $e->getMessage());
            return collect([]);
        }
    }

    public function addBookForUser($userId, $bookId)
    {
        try {
            return UserRecentBook::updateOrCreate(
                ['user_id' => $userId, 'book_id' => $bookId],
                ['read_at' => now()]
            );
        } catch (\Exception $e) {
            Log::error('Error in addBookForUser: ' . $e->getMessage());
            return null;
        }
    }

    private function getCookieRecentBooks()
    {
        try {
            $bookIds = json_decode(Cookie::get(self::COOKIE_NAME, '[]'), true);
            Log::info('Getting books from cookie', ['book_ids' => $bookIds]);

            if (empty($bookIds)) {
                return collect([]);
            }

            return Book::whereIn('id', $bookIds)
                ->where('status', 'active')
                ->where('block', 'no')
                ->get()
                ->sortBy(function ($book) use ($bookIds) {
                    return array_search($book->id, $bookIds);
                });
        } catch (\Exception $e) {
            Log::error('Error in getCookieRecentBooks: ' . $e->getMessage());
            return collect([]);
        }
    }

    public function mergeCookieBooksToUser($userId)
    {
        try {
            $cookieBooks = json_decode(Cookie::get(self::COOKIE_NAME, '[]'), true);

            foreach ($cookieBooks as $bookId) {
                $this->addBookForUser($userId, $bookId);
            }

            Cookie::queue(Cookie::forget(self::COOKIE_NAME));
            Log::info('Merged cookie books to user', ['user_id' => $userId]);
        } catch (\Exception $e) {
            Log::error('Error in mergeCookieBooksToUser: ' . $e->getMessage());
        }
    }

    // Thêm các methods mới
    public function clearRecentBooks()
    {
        try {
            if (Auth::check()) {
                $this->clearUserRecentBooks(Auth::id());
            } else {
                $this->clearCookieRecentBooks();
            }
            Log::info('Cleared all recent books');
            return true;
        } catch (\Exception $e) {
            Log::error('Error clearing recent books: ' . $e->getMessage());
            return false;
        }
    }

    public function removeBook($bookId)
    {
        try {
            if (Auth::check()) {
                $this->removeUserBook(Auth::id(), $bookId);
            } else {
                $this->removeBookFromCookie($bookId);
            }
            Log::info('Removed book from recent', ['book_id' => $bookId]);
            return true;
        } catch (\Exception $e) {
            Log::error('Error removing book: ' . $e->getMessage());
            return false;
        }
    }

    private function clearUserRecentBooks($userId)
    {
        return UserRecentBook::where('user_id', $userId)->delete();
    }

    private function clearCookieRecentBooks()
    {
        Cookie::queue(Cookie::forget(self::COOKIE_NAME));
    }

    private function removeUserBook($userId, $bookId)
    {
        return UserRecentBook::where('user_id', $userId)
            ->where('book_id', $bookId)
            ->delete();
    }

    private function removeBookFromCookie($bookId)
    {
        $recentBooks = json_decode(Cookie::get(self::COOKIE_NAME, '[]'), true);
        $recentBooks = array_values(array_diff($recentBooks, [$bookId]));
        Cookie::queue(self::COOKIE_NAME, json_encode($recentBooks), self::COOKIE_DURATION);
    }
}
