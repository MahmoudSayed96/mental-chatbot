(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.mentalChatbot = {
    attach: function (context, settings) {
      $('main', context).once('mentalChatbot').each(function () {
        // Handling chatbot logo and user avatar.
        var logos = {};
        if (drupalSettings.mental_chatbot.chatbot_logo) {
          logos['chatbotLogo']=drupalSettings.mental_chatbot.chatbot_logo;
        }

        if (drupalSettings.mental_chatbot.chatbot_user_avatar) {
          logos['chatbotUserAvatar'] = drupalSettings.mental_chatbot.chatbot_user_avatar;
        }
        var INDEX = 0;

        $("#chat-submit").click(function(e) {
          e.preventDefault();
          var userMsg = $("#chat-input").val();
          if(userMsg.trim() == ''){
            return false;
          }
          generate_message(userMsg, 'self');
          // Call api server.
          call_server(userMsg);
        });

        function call_server(userInputMsg) {
          $.ajax({
            method: "POST",
            url: "/mental/web/chat",
            data:{'msg':userInputMsg},
            dataType: "json",
            success: function(response) {
              generate_message(response.data.message, 'user');
            }
          });
        }

        function generate_message(msg, type) {
          INDEX++;
          var str=``;
          var img = `<img src="${logos['chatbotLogo']}">`;
          if(type == 'self') {
            img = `<img src="${logos['chatbotUserAvatar']}">`;
          }
          str += `<div id='cm-msg-${INDEX}' class="chat-msg ${type}">
                 <span class="msg-avatar">
                    ${img}
                  </span>
                   <div class="cm-msg-text">${msg}</div>
                </div>`;
          $(".chat-logs").append(str);
          $("#cm-msg-"+INDEX).hide().fadeIn(300);
          if(type == 'self'){
            $("#chat-input").val('');
          }
          $(".chat-logs").stop().animate({ scrollTop: $(".chat-logs")[0].scrollHeight}, 1000);
        }

        // Greeting Message.
        function welcome_message() {
          var welcomeMsg = ($('html').attr('lang') == 'ar') ? "أهلا بك! كيف بإمكاني مساعدتك؟":"Welcome, How can I help you?";
          setTimeout(function() {
            generate_message(welcomeMsg, 'user');
          }, 1000);
        }

        $(document).delegate(".chat-btn", "click", function() {
          var value = $(this).attr("chat-value");
          var name = $(this).html();
          $("#chat-input").attr("disabled", false);
          generate_message(name, 'self');
        });

        // Open chatbot dialog.
        $("#chat-circle").click(function() {
          welcome_message();
          $("#chat-circle").toggle('scale');
          $(".chat-box").toggle('scale');
        });

        // Close chatbot dialog.
        $(".chat-box-toggle").click(function() {
          $("#chat-circle").toggle('scale');
          $(".chat-box").toggle('scale');
        });

      });
    }
  }
})(jQuery, Drupal, drupalSettings);
