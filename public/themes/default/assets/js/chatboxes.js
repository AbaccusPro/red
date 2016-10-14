var chatBoxes = new Vue({
  el: '#chatBoxes',
  data: {
        chatBoxes: [],
        messageBody : '',
        conversations : []
    },
    created : function() {
        this.subscribeToPrivateMessageChannel(current_username);
        this.getConversations();

        $('.chat-conversation-list').bind('scroll',this.chk_scroll);
        $('.following-group').bind('scroll',this.chk_scroll_bottom);
    },
    methods : {
        notify: function(message,type,layout)
        {
            var n = noty({
               text: message,
               layout: 'bottomLeft',
               type : 'success',
               theme : 'relax',
               timeout:1,
               animation: {
                   open: 'animated fadeIn', // Animate.css class names
                   close: 'animated fadeOut', // Animate.css class names
                   easing: 'swing', // unavailable - no need
                   speed: 500 // unavailable - no need
               }
           });
        },
        timeago : function(){


            jQuery.timeago.settings.strings.suffixAgo = "";
            jQuery.timeago.settings.strings.suffixFromNow = "from now";
            jQuery.timeago.settings.strings.inPast = "any moment now";
            jQuery.timeago.settings.strings.seconds = "less than 1m";
            jQuery.timeago.settings.strings.minute = "1m";
            jQuery.timeago.settings.strings.minutes = "%dm";
            jQuery.timeago.settings.strings.hour = "1h";
            jQuery.timeago.settings.strings.hours = "%dh";
            jQuery.timeago.settings.strings.day = "1d";
            jQuery.timeago.settings.strings.days = "%dd";
            jQuery.timeago.settings.strings.month = "1m";
            jQuery.timeago.settings.strings.months = "%dm";
            jQuery.timeago.settings.strings.year = "1y";
            jQuery.timeago.settings.strings.years = "%dy";
            jQuery("time.microtime").timeago();

        },
        autoScroll : function(element)
        {
            $(element).animate({scrollTop: $(element)[0].scrollHeight + 600 }, 2000);
        },
        subscribeToPrivateMessageChannel: function(receiverUsername)
        {

            var vm = this;
            // pusher configuration
            this.pusher = new Pusher(pusherConfig.PUSHER_KEY, {
                encrypted: true,
                auth: {
                    headers: {
                        'X-CSRF-Token': pusherConfig.token
                    },
                    params: {
                        username: "vijay"
                    }
                }
            });

            this.MessageChannel = this.pusher.subscribe(receiverUsername + '-message-created');
            this.MessageChannel.bind('App\\Events\\MessagePublished', function(data) {
                
                indexes = $.map(vm.chatBoxes, function(thread, key) {
                    if(thread.id == data.message.thread_id) {
                        return key;
                    }
                });

                if(indexes[0] >= 0)
                {
                    data.message.user = data.sender;
                    vm.chatBoxes[indexes[0]].conversationMessages.data.push(data.message);
                    vm.autoScroll('.chat-conversation');
                }
                else
                {
                    conversation = [];
                    conversation.id = data.message.thread_id;
                    conversation.user = data.sender;
                    vm.showChatBox(conversation);
                }
            });
        },
        getConversations : function()
        {
            this.$http.post(base_url + 'ajax/get-messages').then( function(response) {
                this.conversations = JSON.parse(response.body).data;
            });
        },
        showConversation : function(conversation)
        {
            if(conversation)
            {
                if(conversation.id != this.currentConversation.id)
                {
                    conversation.unread = false;
                    this.$http.post(base_url + 'ajax/get-conversation/' + conversation.id).then( function(response) {
                        this.currentConversation = JSON.parse(response.body).data;
                        this.currentConversation.user = conversation.user;
                        vm = this;
                        setTimeout(function(){
                            // vm.autoScroll('.coversations-thread');
                            vm.timeago();
                        },100)
                    });    
                }    
            }
            
        },
        postMessage : function(conversation)
        {
            if(conversation.newMessage != '')
            {
                this.$http.post(base_url + 'ajax/post-message/' + conversation.id,{message: conversation.newMessage}).then( function(response) {
                    if(response.status)
                    {
                        conversation.conversationMessages.data.push(JSON.parse(response.body).data);

                        conversation.newMessage="";
                        vm = this;
                        setTimeout(function(){
                            vm.autoScroll('.chat-conversation');
                        },100)

                    }
                });       
            }
            
        },
        showChatBox : function(conversation)
        {
            indexes = $.map(this.chatBoxes, function(thread, key) {
                if(thread.id == conversation.id) {
                    return key;
                }
            });

            

            if(indexes[0] >= 0)
            {
                console.log('prevented second opening of chat box');
            }
            else{
                this.$http.post(base_url + 'ajax/get-conversation/' + conversation.id).then( function(response) {
                    if(response.status)
                    {
                        var chatBox = JSON.parse(response.body).data;
                        chatBox.newMessage = "";
                        chatBox.user = conversation.user;
                        chatBox.minimised = false;
                        this.chatBoxes.push(chatBox);
                        vm = this;
                        setTimeout(function(){
                            vm.autoScroll('.chat-conversation');
                        },100)

                    }
                });       
            }
        },
        sendMessage: function(userid)
        {

            indexes = $.map(this.conversations.data, function(thread, key) {
                if(thread.user)
                {
                    if(thread.user.id == userid) {
                        return key;
                    }    
                }
                
            });

            if(indexes[0] >= 0)
            {
                this.showChatBox(this.conversations.data[indexes[0]]);
            }
            else
            {
                this.$http.post(base_url + 'ajax/get-private-conversation/' + userid).then( function(response) {
                    if(response.status)
                    {
                        this.showChatBox(response.data.data);
                    }
                });       
            }

        },
        chk_scroll : function(e)
        {
            var elem = $(e.currentTarget);
            
            if (elem.scrollTop() == 0) 
            {
                this.getMoreConversationMessages();
            }
        },
        getMoreConversationMessages : function()
        {
            if(this.currentConversation.conversationMessages.data.length < this.currentConversation.conversationMessages.total)
            {
                this.$http.post(this.currentConversation.conversationMessages.next_page_url).then( function(response) {
                    var latestConversations = JSON.parse(response.body).data;

                    
                    this.currentConversation.conversationMessages.last_page =  latestConversations.conversationMessages.last_page;
                    this.currentConversation.conversationMessages.next_page_url =  latestConversations.conversationMessages.next_page_url;
                    this.currentConversation.conversationMessages.per_page =  latestConversations.conversationMessages.per_page;
                    this.currentConversation.conversationMessages.prev_page_url =  latestConversations.conversationMessages.prev_page_url;

                    var vm = this;
                    $.each(latestConversations.conversationMessages.data, function(i, latestConversation) {
                        vm.currentConversation.conversationMessages.data.unshift(latestConversation);
                    });

                    setTimeout(function(){
                        vm.timeago();
                    },10);
                });                      
            }
        },
        chk_scroll_bottom : function(e)
        {
            var elem = $(e.currentTarget);

            if (elem[0].scrollHeight - elem.scrollTop() == elem.outerHeight()) 
            {
                    this.getMoreConversations();    
            }
        },
        getMoreConversations : function()
        {
            if(this.conversations.data.length < this.conversations.total)
            {
                this.$http.post(this.conversations.next_page_url).then( function(response) {
                    var latestConversations = JSON.parse(response.body).data;

                    
                    this.conversations.last_page =  latestConversations.last_page;
                    this.conversations.next_page_url =  latestConversations.next_page_url;
                    this.conversations.per_page =  latestConversations.per_page;
                    this.conversations.prev_page_url =  latestConversations.prev_page_url;
                    

                    var vm = this;
                    $.each(latestConversations.data, function(i, latestConversation) {
                        vm.conversations.data.unshift(latestConversation);
                    });

                    setTimeout(function(){
                        vm.timeago();
                    },10);
                });                      
            }
        }
    }    
});