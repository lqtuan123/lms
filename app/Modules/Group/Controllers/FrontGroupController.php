<?php

namespace App\Modules\Group\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\File;
use App\Modules\Group\Models\Group;
use App\Modules\Group\Models\GroupType;
use Illuminate\Support\Facades\Auth;

class FrontGroupController extends Controller
{
    protected $pagesize;
    public function __construct( )
    {
       
        
    }
    public function frontGroupShow()
    {
        $data['detail'] = \App\Models\SettingDetail::find(1);  
        $data['categories'] = \App\Models\Category::where('status','active')->where('parent_id',null)->get();
        $user = Auth::user();
         ////
         $data['pagetitle']="Danh sách nhóm ITCCTV "  ;
         
         $data['page_up_title'] = "Danh sách nhóm ITCCTV " ;
         $data['page_subtitle']= "Danh sách nhóm ITCCTV "   ;
         $data['page_title']= " " ;
         $data['hotbutton_title'] = "Doanh nghiệp gần bạn nhất"  ;
         $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
         $data['hotbutton_link']= "";
         $data['page_up_title'] = "Danh sách nhóm ITCCTV " ;  
        
        
        $data['groups'] = Group::where('status','active')->paginate(20);
        return view('Group::group.frontindex',$data);
    }
   
}