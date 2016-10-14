<li class="commented delete_comment_list comment-replied">
 <div class="comments"> <!-- main-comment -->
  <div class="commenter-avatar">
    <a href="#"><img src="{{ $reply->user->avatar }}" title="{{ $reply->user->name }}" alt="{{ $reply->user->name }}"></a>
  </div>
  <div class="comments-list">
    <div class="commenter">
      @if($reply->user_id == Auth::user()->id)
      <a href="#" class="delete-comment delete_comment" data-commentdelete-id="{{ $reply->id }}"><i class="fa fa-times"></i></a>
      @endif  
      <div class="commenter-name">
        <a href="{{ url($reply->user->username) }}">{{ $reply->user->name }}</a><span class="comment-description">{{ $reply->description }}</span>
      </div>
      <ul class="list-inline comment-options">
        @if(!$reply->comments_liked->contains(Auth::user()->id))
        <li><a href="#" class="text-capitalize like-comment like" data-comment-id="{{ $reply->id }}">{{ trans('common.like') }}</a></li>
        <li class="hidden"><a href="#" class="text-capitalize like-comment unlike" data-comment-id="{{ $reply->id }}">{{ trans('common.unlike') }}</a></li>
        @else
        <li class="hidden"><a href="#" class="text-capitalize like-comment like" data-comment-id="{{ $reply->id }}">{{ trans('common.like') }}</a></li>
        <li><a href="#" class="text-capitalize like-comment unlike" data-comment-id="{{ $reply->id }}">{{ trans('common.unlike') }}</a></li>
        @endif
        <li>.</li>
        @if($reply->comments_liked->count() != null)
        <li><a href="#" class="show-likes like3-{{ $reply->id }}"><i class="fa fa-thumbs-up"></i>{{ $reply->comments_liked->count() }}</a></li>
        <li class="show-likes like4-{{ $reply->id }} hidden"></li>
        @else
        <li><a href="#" class="show-likes like3-{{ $reply->id }}"><i class="fa fa-thumbs-up"></i>{{ $reply->comments_liked->count() }}</a></li>
        <li class="show-likes like4-{{ $reply->id }} hidden"></li>
        @endif
        <li>.</li>
        <li>
          <time class="post-time timeago" datetime="{{ $reply->created_at }}" title="{{ $reply->created_at }}">{{ $reply->created_at }}</time>
        </li>
      </ul>
    </div>
  </div>
</div><!-- main-comment -->
</li>
