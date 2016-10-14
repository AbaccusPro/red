<form action="<?php echo e(url('')); ?>" method="post" class="create-post-form">
  <?php echo e(csrf_field()); ?>


    <div class="panel panel-default panel-create"> <!-- panel-create -->
        <div class="panel-heading">
            <div class="heading-text">
                <?php echo e(trans('messages.whats-going-on')); ?>

            </div>
        </div>
        <div class="panel-body">        
            <textarea name="description" class="form-control createpost-form comment" cols="30" rows="3" id="createPost" cols="30" rows="2" placeholder="<?php echo e(trans('messages.post-placeholder')); ?>"></textarea>
               

                <div class="user-tags-added" style="display:none">
                    &nbsp; -- <?php echo e(trans('common.with')); ?>

                    <div class="user-tag-names">
                        
                    </div>
                </div>
                <div class="user-tags-addon post-addon" style="display: none">
                    <span class="post-addon-icon"><i class="fa fa-user-plus"></i></span>
                    <div class="form-group">
                        <input type="text" id="userTags" class="form-control user-tags youtube-text" placeholder="<?php echo e(trans('messages.who_are_you_with')); ?>" autocomplete="off" value="" >
                        <div class="clearfix"></div>
                    </div>
                </div>
                <div class="users-results-wrapper"></div>
                <div class="youtube-iframe"></div>
        
                <div class="video-addon post-addon" style="display: none">
                    <span class="post-addon-icon"><i class="fa fa-film"></i></span>
                    <div class="form-group">
                        <input type="text" name="youtubeText" id="youtubeText" class="form-control youtube-text" placeholder="<?php echo e(trans('messages.what_are_you_watching')); ?>"  value="" >
                        <div class="clearfix"></div>
                    </div>
                </div>
              <div class="music-addon post-addon" style="display: none">
                  <span class="post-addon-icon"><i class="fa fa-music" aria-hidden="true"></i></span>
                 <div class="form-group">
                    <input type="text" name="soundCloudText" autocomplete="off" id ="soundCloudText" class="form-control youtube-text" placeholder="<?php echo e(trans('messages.what_are_you_listening_to')); ?>"  value="" >
                    <div class="clearfix"></div>
                 </div>
              </div>
              <div class="soundcloud-results-wrapper">

              </div>
            <div class="location-addon post-addon" style="display: none">
                  <span class="post-addon-icon"><i class="fa fa-map-marker" aria-hidden="true"></i></span>
                 <div class="form-group">
                    <input type="text" name="location" id="pac-input" class="form-control" placeholder="<?php echo e(trans('messages.where_are_you')); ?>"  autocomplete="off" value="" onKeyPress="return initMap(event)"><div class="clearfix"></div>
                 </div>                 
            </div>
              <div class="emoticons-wrapper  post-addon" style="display:none">
                  
              </div>
              <div class="images-selected post-images-selected" style="display:none">
                  <span>3</span> <?php echo e(trans('common.photo_s_selected')); ?>

              </div>
              <!-- Hidden elements  -->
              <input type="hidden" name="timeline_id" value="<?php echo e($timeline->id); ?>">
              <input type="hidden" name="youtube_title" value="">
              <input type="hidden" name="youtube_video_id" value="">
              <input type="hidden" name="locatio" value="">
              <input type="hidden" name="soundcloud_id" value="">
              <input type="hidden" name="user_tags" value="">
              <input type="hidden" name="soundcloud_title" value="">
              <input type="file"   class="post-images-upload hidden" multiple="multiple"  accept="image/jpeg,image/png,image/gif" name="post_images_upload[]" >
              <div id="post-image-holder"></div>
        </div><!-- panel-body -->

        <div class="panel-footer">
            <ul class="list-inline left-list">
                <li><a href="#" id="addUserTags"><i class="fa fa-user-plus"></i></a></li>
                <li><a href="#" id="imageUpload"><i class="fa fa-camera-retro"></i></a></li>
                <li><a href="#" id="musicUpload"><i class="fa fa-music"></i></a></li>
                <li><a href="#" id="videoUpload"><i class="fa fa-film"></i></a></li>
                <li><a href="#" id="locationUpload"><i class="fa fa-map-marker"></i></a></li>
                <li><a href="#" id="emoticons"><i class="fa fa-smile-o"></i></a></li>
            </ul>
            <ul class="list-inline right-list">
                <li><button type="submit" class="btn btn-submit btn-success"><?php echo e(trans('common.post')); ?></button></li>
            </ul>

            <div class="clearfix"></div>
        </div>
    </div>
</form>


<?php if(Setting::get('postcontent_ad') != NULL): ?>
    <div id="link_other" class="page-image">
        <?php echo htmlspecialchars_decode(Setting::get('postcontent_ad')); ?>

    </div>
<?php endif; ?>


<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyD_vuWi_hzMDDeenNYwaNAj0PHzzS2GAx8&libraries=places&callback=initMap"
        async defer></script>

<script>
function initMap(event) 
{    
    var key;  
    var map = new google.maps.Map(document.getElementById('pac-input'), {
    });

    var input = /** @type  {!HTMLInputElement} */(
        document.getElementById('pac-input'));        

    if(window.event)
    {
        key = window.event.keyCode; 

    }
    else 
    {
        if(event)
            key = event.which;      
    }       

    if(key == 13){       
    //do nothing 
    return false;       
    //otherwise 
    } else { 
        var autocomplete = new google.maps.places.Autocomplete(input);  
        autocomplete.bindTo('bounds', map);

    //continue as normal (allow the key press for keys other than "enter") 
    return true; 
    } 
}
</script>




