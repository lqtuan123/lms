<div class="post-actions">
    <div class="motion-container" id="motion-{{$item_id}}" data-id="{{$item_id}}">
        <div class="motion-short">
            <button class="btn-reaction" item_code="{{$item_code}}"  data-id="{{$item_id}}" data-reaction-id="like">
                 <span id='spmcount-{{$item_id}}-{{$item_code}}' class="{{isset($rcount)?'hascomment':''}}">{{isset($rcount)?$rcount:''}}
                    </span> 
                
                @if(isset($rcount))
                    @foreach($reactions as $reaction)
                   {{ $reaction->mcount?  $reaction->icon:''}}  
                    @endforeach
                @else
                👍
                @endif
            </button>
        </div>
        <div class="motion-full">
                @foreach($reactions as $reaction)
                    <button class="btn-reaction" item_code="{{$item_code}}"  data-id="{{$item_id}}" data-reaction-id="{{ $reaction->title }}">
                      <span id="mcount-{{ $reaction->title }}-{{$item_id}}">{{ $reaction->mcount?  $reaction->mcount:'0'}} </span> {{ $reaction->icon }}
                    </button>
                @endforeach
        </div>
         
    </div>
    <div class="add-comment">
        <span style="cursor: pointer;" onclick="toggleCommentBox({{$item_id}})" class="{{$hasComment?'hascomment':''}}">
            {{$hasComment?$hasComment:'💬'}} bình luận</span>
    </div>
    <div class="read-time rating-container" item_code="{{$item_code}}"  data-post-id="{{ $item_id }}">
       <span id="vote-count-{{$item_id}}">{{isset($voteRecord)?$voteRecord->count:''}}</span>
        @for ($i = 1; $i <= 5; $i++)
            <i class="star {{ $i >= (isset($voteRecord)?$voteRecord->point:6) ? 'selected' : '' }}" data-value="{{ $i }}">★</i>
        @endfor
        
    </div>
    
    <div class="bookmark">
        @if($isBookmarked)
            <button class="btn-bookmark bookmarked" item_code="{{$item_code}}" data-post-id="{{ $item_id }}">
                {{$isBookmarked}} <i class="feather icon-feather-bookmark icon-extra-small  " style="background:white">
                </i>
                
            </button>
        @else
            <button class="btn-bookmark" item_code="{{$item_code}}" data-post-id="{{ $item_id }}">
                <i class="feather icon-feather-bookmark icon-extra-small  " style="background:white">
                </i>
            </button>
        @endif
    </div>
</div>


