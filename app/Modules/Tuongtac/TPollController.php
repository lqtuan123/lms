<?php

namespace App\Modules\Tuongtac\Controllers;

use App\Http\Controllers\Controller;
use  App\Modules\Tuongtac\Models\TComment;
use  App\Modules\Tuongtac\Models\TNotice;
use  App\Modules\Tuongtac\Models\TBlog;
use  App\Modules\Tuongtac\Models\TUserpage;
use  App\Modules\Nguoitimviec\Models\JCongviec;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class TPollController extends Controller
{
    // Phương thức chỉ hiển thị giao diện tạm
    public function index() 
    {
        return redirect()->back()->with('info', 'Chức năng này đang được phát triển');
    }
    
    // Phương thức xử lý bình chọn tạm
    public function voteAll() 
    {
        return redirect()->back()->with('info', 'Chức năng này đang được phát triển');
    }
} 