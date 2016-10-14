var vue = new Vue({
  el: '#navbar-right',
  data: {
        notifications: [],
        unreadNotifications: 0,
        notificationsLoaded: false,
        notificationsLoading: false,
        conversations: [],
        posts : [],
        unreadConversations: 0,
        conversationsLoaded: false,
        conversationsLoading: false,
        pusher: [],
    },
    created : function() {
        
        $('.dropdown-messages-list').bind('scroll',this.chk_scroll);

        // Get if there are any unread notifications or conversations
        this.getNotificationsCounter();
        this.getConversationsCounter();

        // init the pusher 
        this.subscribeToNotificationsChannel();
        this.subscribeToMessagesChannel();
    },
    methods : {
        subscribeToNotificationsChannel: function()
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

            this.NotificationChannel = this.pusher.subscribe(current_username + '-notification-created');
            this.NotificationChannel.bind('App\\Events\\NotificationPublished', function(data) {
                vm.unreadNotifications = vm.unreadNotifications + 1;
                data.notification.notified_from = data.notified_from
                if(vm.notifications.data != null)
                {
                    vm.notifications.data.unshift(data.notification);
                }
                vm.notify(data.notification.description);
                $.playSound(theme_url + '/sounds/notification');
                jQuery("time.timeago").timeago();
            });
        },
        subscribeToMessagesChannel: function()
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

            this.MessageChannel = this.pusher.subscribe(current_username + '-message-created');
            this.MessageChannel.bind('App\\Events\\MessagePublished', function(data) {
                vm.unreadConversations = vm.unreadConversations + 1;
                if(vm.conversationsLoaded)
                {
                    vm.conversations.data.unshift(data.message);    
                }
                vm.notify(data.message.body);
                jQuery("time.timeago").timeago();
            });
        },
        getNotificationsCounter : function()
        {
            // Lets get the unread notifications once the Vue instance is ready
            this.$http.post(base_url + 'ajax/get-unread-notifications').then(function(response)  {
                this.unreadNotifications = JSON.parse(response.body).unread_notifications;
            });    
        },
        showNotifications : function()
        {
            if(!this.notificationsLoaded)
            {
                this.notificationsLoading = true;
                this.$http.post(base_url + 'ajax/get-notifications').then( function(response) {
                    this.notifications = JSON.parse(response.body).notifications;
                    setTimeout(function(){
                        jQuery("time.timeago").timeago();
                    },10);
                    this.notificationsLoading = false;
                });
                this.notificationsLoaded = true;
            }
        },
        getMoreNotifications : function()
        {
            if(this.notifications.data.length < this.notifications.total)
            {
                this.notificationsLoading = true;
                this.$http.post(this.notifications.next_page_url).then( function(response) {
                    var latestNotifications = JSON.parse(response.body).notifications;

                    this.notifications.last_page =  latestNotifications.last_page;
                    this.notifications.next_page_url =  latestNotifications.next_page_url;
                    this.notifications.per_page =  latestNotifications.per_page;
                    this.notifications.prev_page_url =  latestNotifications.prev_page_url;

                    var vm = this;
                    $.each(latestNotifications.data, function(i, latestNotification) {
                        vm.notifications.data.push(latestNotification);
                    });
                    this.notificationsLoading = false;
                    setTimeout(function(){
                        jQuery("time.timeago").timeago();
                    },10);
                });                      
            }
        },
        markNotificationsRead : function()
        {

            this.$http.post(base_url + 'ajax/mark-all-notifications').then(function(response)  {
                this.unreadNotifications = 0;
                var vm = this;
                $.map(this.notifications, function(notification, key) {
                    vm.notifications[key].seen = true;
                });
            });     
        },
        getConversationsCounter : function()
        {
            // Lets get the unread  messages once the Vue instance is ready
            this.$http.post(base_url + 'ajax/get-unread-messages').then(function(response)  {
                this.unreadConversations = JSON.parse(response.body).unread_conversations;
            });    
            
        },
        showConversations : function()
        {
            if(!this.conversationsLoaded)
            {
                this.conversationsLoading = true;
                this.$http.post(base_url + 'ajax/get-messages').then( function(response) {
                    this.conversations = JSON.parse(response.body).data;
                    setTimeout(function(){
                        jQuery("time.timeago").timeago();
                    },10);
                    this.conversationsLoaded = true;
                    this.conversationsLoading = false;
                });
                
            }
        },
        getMoreConversations : function()
        {
            this.conversationsLoading = true;

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
                        vm.conversations.data.push(latestConversation);
                    });
                    
                    this.conversationsLoaded = true;
                    this.conversationsLoading = false;
                    
                    setTimeout(function(){
                        jQuery("time.timeago").timeago();
                    },10);
                });                      
            }
        },
        chk_scroll : function(e)
        {
            var elem = $(e.currentTarget);
            if (elem[0].scrollHeight - elem.scrollTop() == elem.outerHeight()) 
            {
                if(elem.data('type')=="notifications")
                {
                    this.getMoreNotifications();    
                }
                else
                {
                    this.getMoreConversations();
                }
                
            }
        },
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
        }
    }    

});