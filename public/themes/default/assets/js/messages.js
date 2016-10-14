var vue = new Vue({
  el: '#messages-page',
  data: {
        conversations: [],
        newConversation : false,
        recipients : [],
        currentConversation: {
            conversationMessages : [],
            user : []
        },
        messageBody : ''
    },
    created : function() {
        this.subscribeToPrivateMessageChannel(current_username);
        this.getConversations();

        $('.coversations-thread').bind('scroll',this.chk_scroll);
        $('.coversations-list').bind('scroll',this.chk_scroll);
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
                
                data.message.user = data.sender;
                if(vm.currentConversation.id ==  data.message.thread_id)
                {
                    vm.currentConversation.conversationMessages.push(data.message);    
                    setTimeout(function(){
                        vm.timeago();
                        vm.autoScroll('.coversations-thread');
                    },100)
                }
                else
                {

                    indexes = $.map(vm.conversations.data, function(thread, key) {
                        if(thread.id == data.message.thread_id) {
                            return key;
                        }
                    });
                    
                    if(indexes != '')
                    { 
                        vm.conversations.data[indexes[0]].unread = true;
                        vm.conversations.data[indexes[0]].lastMessage = data.message;
                    }
                    else
                    {
                        vm.$http.post(base_url + 'ajax/get-message/' + data.message.thread_id).then( function(response) {
                            vm.conversations.data.unshift(response.data.data);    
                        });
                    }
                }
                
            });
        },
        getConversations : function()
        {
            this.$http.post(base_url + 'ajax/get-messages').then( function(response) {
                this.conversations = JSON.parse(response.body).data;
                this.showConversation(this.conversations.data[0]);
            });
        },
        showConversation : function(conversation)
        {
            this.newConversation = false;
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
                            vm.autoScroll('.coversations-thread');
                            vm.timeago();
                        },100)
                    });    
                }    
            }
            
        },
        postMessage : function(conversation)
        {
            
            messageBody = this.messageBody;
            this.messageBody = '';
            this.$http.post(base_url + 'ajax/post-message/' + conversation.id,{message: messageBody}).then( function(response) {
                if(response.status)
                {
                    this.currentConversation.conversationMessages.data.push(JSON.parse(response.body).data);
                    vm = this;
                    $('#messageReceipient').focus();
                    setTimeout(function(){
                        vm.timeago();
                        vm.autoScroll('.coversations-thread');
                    },100)

                }
            });   
            
        },
        postNewConversation : function()
        {
            if(this.recipients.length)
            {
                this.$http.post(base_url + 'ajax/create-message',{message: this.messageBody, recipients : this.recipients}).then( function(response) {
                if(response.status)
                {
                        vm = this;

                        newThread = JSON.parse(response.body).data;
                         indexes = $.map(vm.conversations.data, function(thread, key) {
                            if(thread.id == newThread.id) {
                                return key;
                            }
                        });
                        
                        if(indexes != '')
                        { 
                            vm.conversations.data[indexes[0]].unread = true;
                            vm.conversations.data[indexes[0]].lastMessage = newThread.lastMessage;
                        }
                        else
                        {
                            vm.conversations.data.unshift(response.data.data);
                        }

                        $('#messageReceipient').focus();
                        this.recipients= [];
                        this.newConversation = false;
                        this.messageBody = "";
                        this.showConversation(vm.conversations.data[0]);
                        setTimeout(function(){
                            vm.timeago();
                            vm.autoScroll('.coversations-thread');
                        },100)
                    }
                });        
            }
        },
        autoScroll : function(element)
        {
            $(element).animate({scrollTop: $(element)[0].scrollHeight + 600 }, 2000);
        },
        chk_scroll : function(e)
        {
            var elem = $(e.currentTarget);

            if (elem[0].scrollHeight - elem.scrollTop() == elem.outerHeight()) 
            {
                if(elem.data('type')=="threads")
                {
                    this.getMoreConversations();    
                }
                else
                {
                    this.getMoreConversationMessages();
                }
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
        },
        showNewConversation : function()
        {
            this.newConversation = true;
            this.currentConversation = {
                user : []
            };
            $('#messageReceipient').focus();
            vm = this;
            setTimeout(function(){
                vm.toggleUsersSelectize();
            },10);

        },
        toggleUsersSelectize : function()
        {
            vm = this;
            var selectizeUsers = $('#messageReceipient').selectize({
                valueField: 'id',
                labelField: 'name',
                searchField: 'name',
                render: {
                    option: function(item, escape) {
                        return '<div class="media big-search-dropdown">' + 
                            '<a class="media-left" href="#">' +
                                '<img src="'+ item.avatar + '" alt="...">' +
                            '</a>' +
                        '<div class="media-body">' +
                            '<h4 class="media-heading">' + escape(item.name) + '</h4>' +
                            '<p>' +  item.username +  '</p>' +               '</div>' +
                        '</div>';
                    },
               
               },
               onChange: function(value)
               {
                    $('[name="user_tags"]').val(value);
                    // $('.user-tags-added').find('.user-tag-names').append('<a href="#">' + value  + '</a>');    
                        var selectize =selectizeUsers[0].selectize;
                        vm.recipients = selectize.items;
               },
                load: function(query, callback) {
                   if (!query.length) return callback();
                   $.ajax({
                       url: base_url  + 'api/v1/users',
                       type: 'GET',
                       dataType: 'json',
                       data: {
                           search: query
                       },
                       error: function() {
                           callback();
                       },
                       success: function(res) {
                           callback(res.data);
                       }
                   });
               }
           });
        }
    }    
});