  <?php 
  $display_comment ="";           
  $user_setting =""; 
  $user_follower = $post->chkUserFollower(Auth::user()->id,$post->user_id);
  $user_setting = $post->chkUserSettings($post->user_id);
  

  if($user_follower != NULL)
  {
    if($user_follower == "only_follow") {
      $display_comment = "only_follow";
    }elseif ($user_follower == "everyone") {
      $display_comment = "everyone"; 
    }
  }
  else{
    if($user_setting){
      if($user_setting == "everyone"){
        $user_setting = "everyone";
      }            
    }
  }

  ?>
  <ul class="list-unstyled main-comment comment{{ $comment->id }} @if($comment->replies()->count() > 0) has-replies @endif" id="comment{{ $comment->id }}">    
    <li> 
      <div class="comments delete_comment_list"> <!-- main-comment -->
        <div class="commenter-avatar">
          <a href="#"><img src="{{ $comment->user->avatar }}" title="{{ $comment->user->name }}" alt="{{ $comment->user->name }}"></a>
        </div>
        <div class="comments-list">
          <div class="commenter">
            @if($comment->user_id == Auth::user()->id)
            <a href="#" class="delete-comment delete_comment" data-commentdelete-id="{{ $comment->id }}"><i class="fa fa-times"></i></a>
            @endif
            <div class="commenter-name">
              <a href="{{ url($comment->user->username) }}">{{ $comment->user->name }}</a><span class="comment-description">{{ $comment->description }}</span>
            </div>
            <ul class="list-inline comment-options">

              @if(!$comment->comments_liked->contains(Auth::user()->id))
              <li><a href="#" class="text-capitalize like-comment like" data-comment-id="{{ $comment->id }}">{{ trans('common.like') }}</a></li>
              <li class="hidden"><a href="#" class="text-capitalize like-comment unlike" data-comment-id="{{ $comment->id }}">{{ trans('common.unlike') }}</a></li>
              @else
              <li class="hidden"><a href="#" class="text-capitalize like-comment like" data-comment-id="{{ $comment->id }}">{{ trans('common.like') }}</a></li>
              <li><a href="#" class="text-capitalize like-comment unlike" data-comment-id="{{ $comment->id }}">{{ trans('common.unlike') }}</a></li>
              @endif
              <li>.</li>
              <li><a href="#" class="show-comment-reply">{{ trans('common.reply') }}</a></li>    
              <li>.</li>
              @if($comment->comments_liked->count() != null)
              <li><a href="#" class="show-likes like3-{{ $comment->id }}"><i class="fa fa-thumbs-up"></i>{{ $comment->comments_liked->count() }}</a></li>
              <li class="show-likes like4-{{ $comment->id }} hidden"></li>
              @else
              <li><a href="#" class="show-likes like3-{{ $comment->id }}"><i class="fa fa-thumbs-up"></i>{{ $comment->comments_liked->count() }}</a></li>
              <li class="show-likes like4-{{ $comment->id }} hidden"></li>
              @endif
              <li>.</li>
              <li>
                <time class="post-time timeago" datetime="{{ $comment->created_at }}" title="{{ $comment->created_at }}">{{ $comment->created_at }}</time>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <li>
       @if($display_comment == "only_follow" || $display_comment == "everyone" || $user_setting == "everyone" || $post->user_id == Auth::user()->id)
       <div class="to-comment comment-reply" style="display:none" >  <!-- to-comment -->
        <div class="commenter-avatar">
          <img src="{{ Auth::user()->avatar }}" alt="{{ Auth::user()->name }}" title="{{ Auth::user()->name }}">
        </div>
        <div class="comment-textfield">
          <form action="#" class="comment-form">
            <input class="form-control post-comment" autocomplete="off" data-post-id="{{ $post->id }}" data-comment-id="{{ $comment->id }}" name="post_comment" placeholder="{{ trans('messages.comment_placeholder') }}" rows="1">
          </form>
        </div>
        <div class="clearfix"></div>
      </div><!-- to-comment -->
      @endif 
    </li>
    {{-- replies goes here --}}
    @if($comment->replies()->count() > 0)
    <li>
      <a href="#" class="show-comment-replies replies-count"><i class="fa fa-reply"></i>{{ $comment->replies()->count() }} {{ trans('common.replies') }}</a>
      <div class="comment-replies" style="display:none">
        <ul class="list-unstyled comment-replys"> <!-- comment-replys-list/sub-comment-list -->
          @if($comment->replies()->count() > 0 )
          @foreach($comment->replies as $reply)
          {!! Theme::partial('reply',compact('reply','post')) !!}
          @endforeach
          @endif
        </ul>
      </div>
    </li>
    @endif

  </ul>
</li><!-- replys/sub-comment -->