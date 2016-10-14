<!-- <div class="main-content">-->  
<div class="container">

    <div class="row">
        <div class="col-md-2 visible-lg">
            {!! Theme::partial('home-leftbar',compact('trending_tags')) !!}
        </div>
        <div class="col-lg-10 col-md-12">
            <div class="messages-page" id="messages-page" v-cloak>
                <div class="panel panel-default">
                    <div class="panel-heading no-bg user-pages">
                        <div class="page-heading header-text">
                            {{ trans('common.messages') }} 
                        </div>
                        <div class="user-info-bk">
                            <a href="#" class="btn btn-success pull-right" @click.prevent="showNewConversation">
                                {{ trans('common.create_message') }}
                            </a>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                    <div class="panel-body nopadding">

                        {{-- messagebox --}}
                        <div class="row message-box">
                            <div class="col-md-4 col-sm-4 col-xs-4 message-col-4">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="{{ trans('common.search') }}">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button"><i class="fa fa-search"></i></button>
                                    </span>
                                </div><!-- /input-group -->    
                                <ul class="list-unstyled coversations-list scrollable" @wait-for="getConversations" data-type="threads">
                                    <li class="message-holder" v-bind:class="[ conversation.unread ? 'unseen-message' : '', (conversation.id==currentConversation.id) ? 'active' : '',  ]" v-for="conversation in conversations.data">
                                        <a href="#" class="show-conversation" @click.prevent="showConversation(conversation)">
                                            <div class="media post-list">
                                                <div class="media-left">
                                                    <img v-bind:src="conversation.user.avatar" alt="images"  class="img-radius img-46">
                                                </div>
                                                <div class="media-body">
                                                   
                                                    <h4 class="media-heading">
                                                        @{{ conversation.user.name }}
                                                    </h4>
                                                    <div class="post-text">
                                                        @{{ conversation.lastMessage.body }}
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                </ul>

                                
                            </div>

                            <div class="col-md-8 col-sm-8 col-xs-8 message-col-8">
                                <div class="coversation-tree">
                                    <div class="conversation">
                                        <div class="left-side">
                                            
                                            @{{ currentConversation.user.name }}

                                             <span class="chat-status hidden"></span>
                                            
                                        </div>
                                        <div class="right-side">
                                        </div>
                                        <div class="new-conversation" v-if="newConversation">
                                            <input type="text" v-model="recipients" name="recipients[]" class="form-control" id="messageReceipient" placeholder="{{ trans('messages.search_people_placeholder') }}">
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>

                                        <ul class="list-unstyled coversations-thread "> 

                                                <li class="message-conversation" v-for="message in currentConversation.conversationMessages.data">
                                                <div class="media post-list">
                                                    <div class="media-left">
                                                        <a href="#">
                                                            <img v-bind:src="message.user.avatar" class="img-radius img-40" alt="">
                                                        </a>
                                                    </div>
                                                    <div class="media-body ">
                                                        <h4 class="media-heading"><a href="#">@{{ message.user.name }}</a><span class="text-muted">
                                                            <time class="microtime" datetime="@{{ message.created_at }}" title="@{{ message.created_at }}">
                                                                        @{{ message.created_at }}
                                                                    </time>
                                                        </span></h4>
                                                         <p class="post-text">
                                                            @{{ message.body }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                </div>

                                <div class="input-group new-message">
                                    
                                        <input class="form-control post-message" autocomplete="off" name="message" v-on:keyup.enter="postNewConversation()" v-model="messageBody" v-if="newConversation">
                                        <input class="form-control post-message" autocomplete="off" name="message" v-on:keyup.enter="postMessage(currentConversation)" v-model="messageBody" v-else>
                                        <span class="input-group-btn">
                                        <button class="btn btn-primary" type="button" v-on:click="postMessage(currentConversation)"><i class="fa fa-paper-plane" aria-hidden="true"></i></button>
                                    </span>
                                </div><!-- /input-group -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- </div> -->

{!! Theme::asset()->container('footer')->usePath()->add('messages-js', 'js/messages.js') !!}