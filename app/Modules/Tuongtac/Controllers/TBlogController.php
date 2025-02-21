<?php

namespace App\Modules\Tuongtac\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use  App\Modules\Tuongtac\Models\TBlog;
use  App\Modules\Tuongtac\Models\TTag;
use  App\Modules\Tuongtac\Models\TUserpage;
use  App\Modules\Tuongtac\Models\TPage;
use  App\Modules\Tuongtac\Models\TPageItem;
use  App\Modules\Group\Models\Group;
use  App\Modules\GroupMember\Models\GroupMember;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Modules\Resource\Models\Resource;
use App\Modules\Resource\Models\ResourceType;

class TBlogController extends Controller
{   
    public function getPostContent($slug)
    {
        $post = TBlog::where('slug',$slug)->first();

        if (!$post) {
            return response()->json(['error' => 'Bài viết không tồn tại'], 404);
        }
        $adsense_code = '<ins class="adsbygoogle"
            style="display:block; text-align:center;"
            data-ad-layout="in-article"
            data-ad-format="fluid"
            data-ad-client="ca-pub-5437344106154965"
            data-ad-slot="3375673265"></ins>
        <script>
            (adsbygoogle = window.adsbygoogle || []).push({});
        </script>';

        $content = $post->content;
        // Tìm vị trí của thẻ <p> đầu tiên
        $position = strpos($content, '</p>', strlen($content) / 2); // Sau thẻ </p> gần giữa

        // Nếu tìm thấy vị trí, chèn mã AdSense
        if ($position !== false) {
            $new_content = substr_replace($content, $adsense_code, $position + 4, 0); // +4 vì thêm sau </p>
        } else {
            // Nếu không có <p>, chèn vào giữa
            $new_content = $content . $adsense_code ;
        }
        return response()->json([
            'title' => $post->title,
            'content' => $new_content,
        ]);
    }
    public function myblog()
    {
        $data['detail'] = \App\Models\SettingDetail::find(1);  
        $data['categories'] = \App\Models\Category::where('status','active')->where('parent_id',null)->get();
        $user  = auth()->user();
        if(!$user)
        {
            return redirect()->route('front.login');
        }
         ////
         $data['pagetitle']="Cộng đồng itcctv - bài viết của tôi" ;
         $data['pagebreadcrumb'] ='<nav aria-label="breadcrumb" class="theme-breadcrumb">
         <ol class="breadcrumb">
             <li class="breadcrumb-item"><a href="">Trang chủ</a></li>';
         
         $data['pagebreadcrumb'] .='  </ol> </nav>';
         $data['page_up_title'] = "Cộng đồng itcctv - bài viết của tôi" ;
         $data['page_subtitle']="Cộng đồng itcctv - bài viết của tôi" ;
         $data['page_title']= " " ;
         $data['hotbutton_title'] = "Doanh nghiệp gần bạn nhất"  ;
         $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
         $data['hotbutton_link']= "";
         $data['page_up_title'] ="Cộng đồng itcctv - bài viết của tôi" ;
       
        $data['posts']   = TBlog::where('user_id',$user->id)
                          ->orderBy('id','desc')->paginate(40);
        
        $data['item_code'] = 'tblog';
        foreach ($data['posts'] as $blog)
        {

            $blog->user_url  = TPage::getPageUrl( $blog->user_id,'user');
            // $userpage = TUserpage::where('user_id',$blog->user_id)->first();
            // if($userpage)
            // {
            //     $blog->user_url  = route('front.userpages.viewuser',$userpage->slug);
            //     // dd($data['user_url']);
            // }

            // $data['user_url'] = "";
            // $userpage = TUserpage::where('user_id',$blog->user_id)->first();
            // if($userpage)
            // {
            //     $data['user_url'] = route('front.userpages.viewuser',$userpage->slug);
            // }
            $blog->tags = \DB::table('t_tags')
                ->join('t_tag_items', 't_tags.id', '=', 't_tag_items.tag_id')
                ->where('t_tag_items.item_id', $blog->id)
                ->where('t_tag_items.item_code', 'tblog') // Nếu cần lọc theo loại bài viết
                ->select('t_tags.*')
                ->get();

            $blog->author = \App\Models\User::find($blog->user_id);
            $blog->commenthtml =\App\Modules\Tuongtac\Controllers\TCommentController::getCommentActive($blog->id, 'tblog' );
            $blog->actionbar =\App\Modules\Tuongtac\Controllers\TuongtacController::getActionBar($blog->id, 'tblog' );
            
        }
        $data['script_actionbar'] = \App\Modules\Tuongtac\Controllers\TuongtacController::getSctiptActionBar( );
        return view('Tuongtac::frontend.blogs.index',$data);
    }
    public function show($slug)
    {
        $tblog = TBlog::where('slug',$slug)->first();
        
        if(!$tblog)
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
        $data['detail'] = \App\Models\SettingDetail::find(1);  
        $data['categories'] = \App\Models\Category::where('status','active')->where('parent_id',null)->get();
        $user  = auth()->user();
         ////
         $data['pagetitle']="" .$tblog->title;
         $data['page_up_title'] = "" .$tblog->title;  ;
         $data['page_subtitle']="" .$tblog->title;   ;
         $data['page_title']= " " ;
         $data['hotbutton_title'] = "Doanh nghiệp gần bạn nhất"  ;
         $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
         $data['hotbutton_link']= "";
         $data['page_up_title'] ="" .$tblog->title;  
        

         $data['menutags'] = TTag::where('slug','<>','rao-vat')->orderBy('hit')->limit(10)->get();
         
         $data['item_code'] = 'tblog';
         $data['post'] =  $tblog;
         $data['post']->tags = \DB::table('t_tags')
                 ->join('t_tag_items', 't_tags.id', '=', 't_tag_items.tag_id')
                 ->where('t_tag_items.item_id', $tblog->id)
                 ->where('t_tag_items.item_code', 'tblog') // Nếu cần lọc theo loại bài viết
                 ->select('t_tags.*')
                 ->get();
 

        $data['keyword'] = "";
        foreach($data['post']->tags  as $tag)
        {
            $data['keyword'].= $tag->title . ",";
        }
        $data['description'] =  $data['keyword'];
            if ($data['post']->photo && $data['post']->photo != '') {
            $photos = json_decode($data['post']->photo, true); // Thêm `true` để decode thành mảng.
            if (is_array($photos) && count($photos) > 0) {
                $data['ogimage'] = $photos[0];
            }
        } 
                 
        
        $data['post']->author = \App\Models\User::find($tblog->user_id);
        $data['post']->commenthtml =\App\Modules\Tuongtac\Controllers\TCommentController::getCommentActive($tblog->id, 'tblog' );
        $data['post']->actionbar =\App\Modules\Tuongtac\Controllers\TuongtacController::getActionBar($tblog->id, 'tblog' );
        $data['post']->user_url  = TPage::getPageUrl(  $data['post']->user_id,'user');
        // $data['post']->user_url  = "";
        // $userpage = TUserpage::where('user_id', $data['post']->user_id)->first();
        // if($userpage)
        // {
        //     $data['post']->user_url  = route('front.userpages.viewuser',$userpage->slug);
        //     // dd($data['user_url']);
        // }
        $data['script_actionbar'] = \App\Modules\Tuongtac\Controllers\TuongtacController::getSctiptActionBar( );
        
        if($data['post']->resources)
            $data['link_download'] = \App\Modules\Resource\Controllers\FileDownloadController::downloadform($data['post']->resources );
         return view('Tuongtac::frontend.blogs.show',$data);

    }
    public function favblog()
    {
        $user  = auth()->user();
        if(!$user)
        {
            return redirect()->route('front.login');
        }
        $data['detail'] = \App\Models\SettingDetail::find(1);  
        $data['categories'] = \App\Models\Category::where('status','active')->where('parent_id',null)->get();
        $user  = auth()->user();
         ////
         $data['pagetitle']="Cộng đồng itcctv - bài viết yêu thích" ;
         $data['pagebreadcrumb'] ='<nav aria-label="breadcrumb" class="theme-breadcrumb">
         <ol class="breadcrumb">
             <li class="breadcrumb-item"><a href="">Trang chủ</a></li>';
         
         $data['pagebreadcrumb'] .='  </ol> </nav>';
         $data['page_up_title'] = "Cộng đồng itcctv - bài viết yêu thích" ;
         $data['page_subtitle']="Cộng đồng itcctv - bài viết yêu thích" ;
         $data['page_title']= " " ;
         $data['hotbutton_title'] = "Doanh nghiệp gần bạn nhất"  ;
         $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
         $data['hotbutton_link']= "";
         $data['page_up_title'] ="Cộng đồng itcctv - bài viết yêu thích" ;
       
      
        // $data['posts'] = \DB::table('t_blogs')
        //     ->join('t_tag_items', 't_blogs.id', '=', 't_tag_items.item_id')
        //     ->where('t_tag_items.item_code', 'tblog') // Lọc theo item_code (nếu cần)
        //     ->where('t_tag_items.tag_id', $tag->id)   // Lọc theo tag_id
        //     ->select('t_blogs.*')
        //     ->paginate(10);
        $data['posts']   =\DB::table('t_blogs')
        ->select ('t_blogs.*','u.dem' )
        ->where('status','1')
        ->leftJoin(\DB::raw('( select count(id) as dem,item_id from t_recommends where item_code="tblog" group by item_id ) as u'),'t_blogs.id','=','u.item_id')
        ->orderBy('u.dem','desc')->paginate(40);
        // $posts = Post::whereHas('tags', function ($query) use ($tagName) {
        //     $query->where('name', $tagName);
        // })->paginate(10);

        $data['menutags'] = TTag::where('slug','<>','rao-vat')->orderBy('hit')->limit(10)->get();
        $data['item_code'] = 'tblog';
        foreach ($data['posts'] as $blog)
        {
            $blog->user_url  = TPage::getPageUrl(  $blog->user_id,'user');
      
            // $blog->user_url  = "";
            // $userpage = TUserpage::where('user_id',$blog->user_id)->first();
            // if($userpage)
            // {
            //     $blog->user_url  = route('front.userpages.viewuser',$userpage->slug);
            //     // dd($data['user_url']);
            // }
            
            $blog->tags = \DB::table('t_tags')
                ->join('t_tag_items', 't_tags.id', '=', 't_tag_items.tag_id')
                ->where('t_tag_items.item_id', $blog->id)
                ->where('t_tag_items.item_code', 'tblog') // Nếu cần lọc theo loại bài viết
                ->select('t_tags.*')
                ->get();

            $blog->author = \App\Models\User::find($blog->user_id);
            $blog->commenthtml =\App\Modules\Tuongtac\Controllers\TCommentController::getCommentActive($blog->id, 'tblog' );
            $blog->actionbar =\App\Modules\Tuongtac\Controllers\TuongtacController::getActionBar($blog->id, 'tblog' );
            
        }
        $data['script_actionbar'] = \App\Modules\Tuongtac\Controllers\TuongtacController::getSctiptActionBar( );
        return view('Tuongtac::frontend.blogs.index',$data);
    }
    public function tag(Request $request,$tag)
    {
        $tag = TTag::where('slug',$tag)->first();
        if(!$tag)
        {
            return back()->with('error','Không tìm thấy dữ liệu');
        }
        $data['detail'] = \App\Models\SettingDetail::find(1);  
        $data['categories'] = \App\Models\Category::where('status','active')->where('parent_id',null)->get();
        $user  = auth()->user();
         ////
         $data['pagetitle']="Cộng đồng itcctv - bài viết " .$tag->title;
         $data['pagebreadcrumb'] ='<nav aria-label="breadcrumb" class="theme-breadcrumb">
         <ol class="breadcrumb">
             <li class="breadcrumb-item"><a href="">Trang chủ</a></li>';
         
         $data['pagebreadcrumb'] .='  </ol> </nav>';
         $data['page_up_title'] = "Cộng đồng itcctv - bài viết " .$tag->title;  ;
         $data['page_subtitle']="Cộng đồng itcctv - bài viết " .$tag->title;   ;
         $data['page_title']= " " ;
         $data['hotbutton_title'] = "Doanh nghiệp gần bạn nhất"  ;
         $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
         $data['hotbutton_link']= "";
         $data['page_up_title'] ="Cộng đồng itcctv - bài viết " .$tag->title;  
        
        
      
        // $data['posts'] = \DB::table('t_blogs')
        //     ->join('t_tag_items', 't_blogs.id', '=', 't_tag_items.item_id')
        //     ->where('t_tag_items.item_code', 'tblog') // Lọc theo item_code (nếu cần)
        //     ->where('t_tag_items.tag_id', $tag->id)   // Lọc theo tag_id
        //     ->where('t_blogs.status',1)
        //     ->select('t_blogs.*')
        //     ->orderBy('t_blogs.id','desc')
        //     ->paginate(40);

        $data['posts'] = \DB::table('t_blogs')
        ->join('t_tag_items', 't_blogs.id', '=', 't_tag_items.item_id')
        ->where('t_tag_items.item_code', 'tblog') // Lọc theo item_code (nếu cần)
        ->where('t_tag_items.tag_id', $tag->id)   // Lọc theo tag_id
        ->where('t_blogs.status',1)
        ->select('t_blogs.*')
        ->inRandomOrder() // Sắp xếp ngẫu nhiên
        ->paginate(40);

        $data['menutags'] = TTag::where('slug','<>','rao-vat')->orderBy('hit')->limit(10)->get();
        $data['item_code'] = 'tblog';
        foreach ($data['posts'] as $blog)
        {
            $blog->user_url  = TPage::getPageUrl(  $blog->user_id,'user');
      
            // $blog->user_url  = "";
            // $userpage = TUserpage::where('user_id',$blog->user_id)->first();
            // if($userpage)
            // {
            //     $blog->user_url  = route('front.userpages.viewuser',$userpage->slug);
            //     // dd($data['user_url']);
            // }

            $blog->tags = \DB::table('t_tags')
                ->join('t_tag_items', 't_tags.id', '=', 't_tag_items.tag_id')
                ->where('t_tag_items.item_id', $blog->id)
                ->where('t_tag_items.item_code', 'tblog') // Nếu cần lọc theo loại bài viết
                ->select('t_tags.*')
                ->get();

            $blog->author = \App\Models\User::find($blog->user_id);
            $blog->commenthtml =\App\Modules\Tuongtac\Controllers\TCommentController::getCommentActive($blog->id, 'tblog' );
            $blog->actionbar =\App\Modules\Tuongtac\Controllers\TuongtacController::getActionBar($blog->id, 'tblog' );
            
        }
        $data['script_actionbar'] = \App\Modules\Tuongtac\Controllers\TuongtacController::getSctiptActionBar( );
        return view('Tuongtac::frontend.blogs.index',$data);
    }
    public function index(Request $request)
    {
        $data['detail'] = \App\Models\SettingDetail::find(1);  
        //$data['categories'] = \App\Models\Category::where('status','active')->where('parent_id',null)->get();
        $user  = auth()->user();
         ////
         $data['pagetitle']="Cộng đồng itcctv " ;
         $data['pagebreadcrumb'] ='<nav aria-label="breadcrumb" class="theme-breadcrumb">
         <ol class="breadcrumb">
             <li class="breadcrumb-item"><a href="">Trang chủ</a></li>';
         
         $data['pagebreadcrumb'] .='  </ol> </nav>';
         $data['page_up_title'] = 'Cộng đồng itcctv'  ;
         $data['page_subtitle']="Cộng đồng itcctv"   ;
         $data['page_title']= " " ;
         $data['hotbutton_title'] = "Cộng đồng itcctv";
         $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
         $data['hotbutton_link']= "";
         $data['page_up_title'] = "Cộng đồng itcctv "  ;
         $search = $request->input('search'); // Lấy giá trị tìm kiếm từ form

        // //  $data['posts'] = TBlog::all();
        //  $data['posts'] = TBlog::when($search, function ($query, $search) {
        //     return $query->where('title', 'LIKE', "%{$search}%")
        //                  ->orWhere('content', 'LIKE', "%{$search}%");
        // })->where('status',1)->orderBy('id','desc')->paginate(40);
       
        $data['posts'] = TBlog::when($search, function ($query, $search) {
            return $query->where('title', 'LIKE', "%{$search}%")
                         ->orWhere('content', 'LIKE', "%{$search}%");
        })
        ->where('status', 1)
        ->inRandomOrder() // Sắp xếp ngẫu nhiên
        ->paginate(40);

        $data['menutags'] = TTag::where('slug','<>','rao-vat')->orderBy('hit')->limit(10)->get();
        $data['item_code'] = 'tblog';
        foreach ($data['posts'] as $blog)
        {
            $blog->user_url  = TPage::getPageUrl(  $blog->user_id,'user');
      
            // $blog->user_url  = "";
            // $userpage = TUserpage::where('user_id',$blog->user_id)->first();
            // if($userpage)
            // {
            //     $blog->user_url  = route('front.userpages.viewuser',$userpage->slug);
            //     // dd($data['user_url']);
            // }
            $blog->commenthtml =\App\Modules\Tuongtac\Controllers\TCommentController::getCommentActive($blog->id, 'tblog' );
            $blog->actionbar =\App\Modules\Tuongtac\Controllers\TuongtacController::getActionBar($blog->id, 'tblog' );
            
        }
        $data['script_actionbar'] = \App\Modules\Tuongtac\Controllers\TuongtacController::getSctiptActionBar( );
        return view('Tuongtac::frontend.blogs.index',$data);
    }
    public function addpageblog($id)
    {
        $user  = auth()->user();
        if(!$user)
        {
            return redirect()->route('front.login');
        }
        $data['page_id'] = $id;
        $data['detail'] = \App\Models\SettingDetail::find(1);  
        $data['categories'] = \App\Models\Category::where('status','active')->where('parent_id',null)->get();
        $user  = auth()->user();
         ////
         $data['pagetitle']="Cộng đồng itcctv" ;
         $data['pagebreadcrumb'] ='<nav aria-label="breadcrumb" class="theme-breadcrumb">
         <ol class="breadcrumb">
             <li class="breadcrumb-item"><a href="">Trang chủ</a></li>';
         
         $data['pagebreadcrumb'] .='  </ol> </nav>';
         $data['page_up_title'] = 'Cộng đồng itcctv'  ;
         $data['page_subtitle']="Cộng đồng itcctv"   ;
         $data['page_title']= " " ;
         $data['hotbutton_title'] = "Cộng đồng itcctv";
         $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
         $data['hotbutton_link']= "";
         $data['page_up_title'] = "Cộng đồng itcctv "  ;
         $data['tags'] = TTag::orderBy('title','ASC')->get();
        $data['toptags']  =  TTag::where('id','<=',5)->orderBy('hit')->get();
        
        return view('Tuongtac::frontend.blogs.create',$data);
    }
    public function check_quyendangbai($page)
    {
        $user = auth()->user();
        if(!$user)
            return 0;
        $is_create = 0;
        if($page->item_code=='group')
        {
            $group = Group::find($page->item_id);
            if(! $group)
            {
                return 0;
            }
            else
            {
                // dd($group,$group->getRole($user->id));
                if( $user &&  $group->getRole($user->id) != '')
                {
                    $data['role'] = $group->getRole($user->id);
                    $is_create = 1;
                }
                
            }
        }
        if($page->item_code=='user')
        {
            if($page->item_id == auth()->id())
            {
                $is_create = 1;
            }
        }
        if( $user->role=='admin')
        {
            $is_create = 1;
        }
        return $is_create ;
    }
    public function addgroupblog($id)
    {
        $user  = auth()->user();
        if(!$user)
        {
            return redirect()->route('front.login');
        }
        $page = TPage::find($id);
        if(!$page  )
        {
            return redirect()->back()->with('error','Không tìm thấy trang!');
        }
        $is_create = $this->check_quyendangbai($page);
      
        return $this->addpageblog($page->id);
    }
    public function create()
    {
        $user  = auth()->user();
        if(!$user)
        {
            return redirect()->route('front.login');
        }
        $page = TPage::where('item_id',$user->id)->where('item_code','user')->first();
        if (!$page)
        {
            $slug = Str::slug($user->full_name);
            $ppage = TPage::where('slug',$slug)->first();
            if($ppage)
            {
                $slug .= uniqid();
            }
            $data['item_id'] = $user->id;
            $data['item_code'] = 'user';
            $data['title'] = $user->full_name;
            $data['slug'] = $slug;
            $data['description'] ="";
            $data['banner'] = "https://itcctv.vn/images/profile-8.jpg";
            $data['avatar'] = $user->photo?$user->photo:"https://itcctv.vn/images/profile-8.jpg";
            $data['status'] = "active";
            $page = TPage::create($data);
        }
        return $this->addpageblog($page->id);
    }
    public function store(Request $request)
    {
        $user  = auth()->user();
        if(!$user)
        {
            return redirect()->route('front.login');
        }
        // Xác thực dữ liệu
        $request->validate([
            'title' => 'required|max:255',
            'content' => 'required',
            'photo' => 'string|nullable',
            'tags' => 'nullable|array', // Tags phải là một mảng
            'urls' => 'nullable|array', // Tags phải là một mảng
            'document' => 'nullable|array',
            'page_id' => 'nullable|integer',
            'document.*' => 'file|mimes:jpg,jpeg,png,mp4,mp3,pdf,doc,mov,docx,ppt,pptx,xls,xlsx,zip,rar,txt|max:2048',
        ]);
        
        $page = TPage::find($request->page_id);
        if(!$page)
        {
            return redirect()->back()->with('error','Bài đăng không thuộc trang nào!');

        }
        $data = $request->all();
        $slug = Str::slug($request->input('title'));
        $slug_count = TBlog::where('slug',$slug)->count();
        if($slug_count > 0)
        {
            $slug .= time();
        }
        $data['slug'] = $slug;
        $data['hit'] = 0;
        $data['user_id'] = auth()->id();
         /// ------end replace --///
        $helpController = new \App\Http\Controllers\Frontend\HelperController();
        $data['content'] = $helpController->uploadImageInContent( $data['content'] );

        // Lưu bài viết vào database
        $post = TBlog::create( $data);
        $tag_ids = $request->tags;
        $tagcontroller = new \App\Modules\Tuongtac\Controllers\TTagController();
        $tagcontroller->store_item_tag($post->id, $tag_ids,'tblog');
        //ad point to user
        TUserpage::add_points(auth()->id(),10);
        $resourceIds = array();
        if ($request->hasFile('document')) {
            foreach ($request->file('document') as $file) {
                $resourceIds[] = Resource::createResource($request, $file, 'TBlog')->id;
            }
          
        }
        $urls = $request->urls;
        if($urls )
        {
            foreach ($urls as $url)
            {
                if($url)
                    $resourceIds[] = Resource::createUrlResource(uniqid(), $url, 'other','tblog')->id;
            }
        }
        $post->resources =  $resourceIds;
        $post->save();
        if($request->page_id)
        {
            $page = TPage::find($request->page_id);
            if($page)
            {
                if($this->check_quyendangbai($page))
                {
                    $datam['item_id']= $post->id;
                    $datam['item_code']= 'tblog';
                    $datam['page_id']= $page->id;
                    $item = TPageItem::create($datam);
                    $item->order_id = $item->id;
                    $item->save();
                }
              
            }
        }
        if(isset($page) )
            return redirect()->route('front.tpage.view',$page->slug)->with('success', 'Bài viết đã được thêm thành công!');
        return redirect()->route('front.tblogs.index')->with('success', 'Bài viết đã được thêm thành công!');
    }
    public function status($id)
    {
        $tblog = TBlog::findOrFail($id);
        if($tblog->user_id != auth()->id())
        {
            return back()->with('error','Không có quyền thay đổi!');
        }
        else
        {
            $tblog->status = ($tblog->status + 1)%2;
            $tblog->save();
            return back()->with('success','đã cập nhật thành công!');
        }
    }
     
    public function edit(Request $request, $id)
    {
        $user  = auth()->user();
        if(!$user)
        {
            return redirect()->route('front.login');
        }
        $data['detail'] = \App\Models\SettingDetail::find(1);  
        $data['categories'] = \App\Models\Category::where('status','active')->where('parent_id',null)->get();
        $user  = auth()->user();
         ////
         $data['pagetitle']="Cộng đồng itcctv" ;
         $data['pagebreadcrumb'] ='<nav aria-label="breadcrumb" class="theme-breadcrumb">
         <ol class="breadcrumb">
             <li class="breadcrumb-item"><a href="">Trang chủ</a></li>';
         
         $data['pagebreadcrumb'] .='  </ol> </nav>';
         $data['page_up_title'] = 'Cộng đồng itcctv'  ;
         $data['page_subtitle']="Cộng đồng itcctv"   ;
         $data['page_title']= " " ;
         $data['hotbutton_title'] = "Cộng đồng itcctv";
         $data['hotbutton_subtitle'] = "được xác nhận bởi itcctv";
         $data['hotbutton_link']= "";
         $data['page_up_title'] = "Cộng đồng itcctv "  ;
         $data['tags'] = TTag::orderBy('title','ASC')->get();
        $data['post'] = TBlog::findOrFail($id);
       
        // Kiểm tra nếu người dùng không phải tác giả
        if (auth()->id() ==  $data['post']->author->id || auth()->user()->role=="admin") {
            $data['toptags']  =  TTag::where('id','<=',5)->orderBy('hit')->get();
            $data['images'] = json_decode( $data['post']->photo, true); // Giải mã JSON thành mảng
     
            if( $data['post']->resources)
            { 
                $resources = Resource::whereIn('id', $data['post']->resources)->get();
                $data['resources']  =  $resources ;
            }
            if($request->frompage)
                $data['frompage'] = $request->frompage;
            return view('Tuongtac::frontend.blogs.edit',  $data);
        }
        else
        {
            abort(403, 'Bạn không có quyền chỉnh sửa bài viết này.');
        }
    
    }
    public function destroy($id)
    {
        $user  = auth()->user();
        if(!$user)
        {
            return redirect()->route('front.login');
        }
        $post = TBlog::findOrFail($id);
        // Kiểm tra nếu người dùng không phải tác giả
        if (auth()->id() == $post->user_id || auth()->user()->role=='admin') {
            $post->delete();
            return redirect()->route('front.tblogs.index')->with('success', 'Bài viết đã được xóa!');
        }
        else
        {
            abort(403, 'Bạn không có quyền xóa bài viết này.');
        }
    }

   
    public function update(Request $request, $id)
    {
        $user  = auth()->user();
        if(!$user)
        {
            return redirect()->route('front.login');
        }
        // Kiểm tra nếu người dùng không phải tác giả
        $post = TBlog::find($id);
        // dd(auth()->id() , $post->user_id , auth()->user()->role);
        if (auth()->id() == $post->user_id || auth()->user()->role=='admin') {
            $request->validate([
                'title' => 'required|max:255',
                'content' => 'required',
                'photo' => 'string|nullable',
                'tags' => 'nullable|array', // Tags phải là một mảng
                'document' => 'nullable|array',
                'document.*' => 'file|mimes:jpg,jpeg,png,mp4,mp3,pdf,doc,mov,docx,ppt,pptx,xls,xlsx,zip,rar,txt|max:20480',
       
            ]);
            $data=$request->all();
            // dd($data);
            $tag_ids = $request->tags;
            $data['toptags']  =  TTag::where('id','<=',5)->orderBy('hit')->get();
            $helpController = new \App\Http\Controllers\Frontend\HelperController();
            $data['content'] = $helpController->uploadImageInContent( $data['content'] );
          
            $post->update($data);
            $tagcontroller = new \App\Modules\Tuongtac\Controllers\TTagController();
            $tagcontroller->update_item_tag($post->id, $tag_ids,'tblog');
            $newResourceIds = [];
            
            if ($request->hasFile('document')) {
                foreach ($request->file('document') as $file) {
                    $resource = Resource::createResource($request, $file, 'Book');
                    $newResourceIds[] = $resource->id;
                }
                $post->resources =  $newResourceIds;
                $post->save();
            }
            if($request->frompage )
            {
                $page = TPage::where('slug',$request->frompage)->first();
                if($page)
                    return redirect()->route('front.tpage.view',$request->frompage)->with('success', 'Bài viết đã được cập nhật!');
     
            }
            return redirect()->route('front.tblogs.show',$post->slug)->with('success', 'Bài viết đã được cập nhật!');
      
        }
        else
        {
            abort(403, 'Bạn không có quyền chỉnh sửa bài viết này.');
        }
    }
}