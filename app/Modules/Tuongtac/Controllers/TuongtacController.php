<?php

namespace App\Modules\Tuongtac\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Modules\Tuongtac\Models\TComment;
use App\Modules\Tuongtac\Models\TNotice;
use App\Modules\Tuongtac\Models\TBlog;
use App\Modules\Tuongtac\Models\TTag;
use App\Modules\Tuongtac\Models\TTagItem;
use App\Modules\Tuongtac\Models\TMotion;
use App\Modules\Tuongtac\Models\TMotionItem;
use App\Modules\Tuongtac\Models\TRecommend;
use App\Modules\Tuongtac\Models\TVoteItem;
use App\Modules\Tuongtac\Services\SocialService;
use Illuminate\Support\Str;

class TuongtacController extends Controller
{
    protected $socialService;
    
    public function __construct(SocialService $socialService)
    {
        $this->socialService = $socialService;
    }
    
    public function index()
    {
        return view('Tuongtac::frontend.index');
    }
    
    public static function getSctiptActionBar()
    {
        return view('Tuongtac::frontend.actionbar.script')->render();
    }

    public function getVotes($itemId)
    {
       
    }

    /**
     * Generate HTML for actionbar of an item
     * 
     * @param int $item_id
     * @param string $item_code
     * @param string $slug Optional slug for sharing
     * @return string HTML
     */
    public static function getActionBar($item_id, $item_code, $slug = null)
    {
        // Lấy service từ container
        $socialService = app(SocialService::class);
        
        // Check if user has bookmarked the item
        if ($userId = Auth::id()){
            $mark = DB::select('select count(id) as tong from t_recommends where user_id='.$userId.' and item_id ='.$item_id.' and item_code="'.$item_code.'"');
            if($mark[0]->tong == 0){
                $data['isBookmarked'] = 0;
            }
            else{
                $data['isBookmarked'] = 1;
            }
        } else {
            $data['isBookmarked'] = 0;
        }

        // Basic data
        $data['item_id'] = $item_id;
        $data['item_code'] = $item_code;
        $data['slug'] = $slug;
        
        // Bookmark data
        $data['isBookmarked'] = TRecommend::hasBookmarked($item_id, $item_code);
        $recommends = DB::select('select count(id) as tong from t_recommends where item_id ='.$item_id.' and item_code="'.$item_code.'"');
        
        if($recommends[0]->tong == 0)
            $data['tong'] = null;
        else
            $data['tong'] = $recommends[0]->tong;

        // Reaction data
        $data['reactions'] = TMotion::all();
        $motion = TMotionItem::where('item_id', $item_id)->where('item_code', $item_code)->first();
      
        if($motion)
        {
            $data['rcount'] = 0;
            foreach ($motion->motions as $key => $mcount)
            {
                $data['rcount'] += $mcount;
            }
            
            $reactionCount = $motion->motions;
            foreach ($reactionCount as $key => $mcount)
            {
                foreach($data['reactions'] as $reaction)
                {
                    if($reaction->title == $key)
                    {
                        $reaction->mcount = $mcount;
                    }
                }
            }
        }
        
        // Voting data
        $data['voteRecord'] = DB::table('t_vote_items')->where('item_id', $item_id)->first();
        
        // Comment data
        $comments = DB::select('select count(id) as tong from t_comments where item_id ='.$item_id.' and item_code="'.$item_code.'"');
        if($comments[0]->tong == 0)
            $data['hasComment'] = 0;
        else
            $data['hasComment'] = $comments[0]->tong;
            
        // Social interactions data from service
        $data['socialInteractions'] = $socialService->getInteractions($item_id, $item_code);
        $data['socialInteractionsHtml'] = $socialService->getInteractionsHtml($item_id, $item_code, $slug);

        $html = view('Tuongtac::frontend.actionbar.show', $data)->render();
        return $html;
    }
    
    /**
     * Process a share action
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function processShare(Request $request)
    {
        $request->validate([
            'item_id' => 'required|integer',
            'item_code' => 'required|string',
        ]);
        
        // Cộng điểm khi chia sẻ bài viết
        if (Auth::check()) {
            Auth::user()->addPoint('share_post', $request->item_id, $request->item_code);
        }
        
        $result = $this->socialService->recordShare(
            $request->item_id,
            $request->item_code,
            Auth::id()
        );
        
        return response()->json($result);
    }
}