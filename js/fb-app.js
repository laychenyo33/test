jQuery(function($){
//    function getLoginedAuthInfo(response){
//        A_uid = response.authResponse.userID;
//        A_accessToken = response.authResponse.accessToken;
//        A_page_id = "391099847708485";
//        A_page_access_token = null;
//        FB.api('/me/accounts', 'get',  function(response) {
//            if(response.data !== undefined){
//                for(var k=0;k<response.data.length;k++){
//                    if(response.data[k].id==A_page_id){
//                        A_page_access_token = response.data[k].access_token;
//                        $("#postToFb,#photoToFb,#fbContent").show();
//                        break;
//                    }
//                }
//            }
//        });          
//    }
    function updateStatusCallback(response){
          if (response.status === 'connected') {
            // the user is logged in and has authenticated your
            // app, and response.authResponse supplies
            // the user's ID, a valid access token, a signed
            // request, and the time the access token 
            // and signed request each expire
            getLoginedAuthInfo(response);
          } else if (response.status === 'not_authorized') {
            // the user is logged in to Facebook, 
            // but has not authenticated your app
            $("#login-fb").show();
          } else {
            $("#login-fb").show();
            // the user isn't logged in to Facebook.
          }
    }
    $.ajaxSetup({ cache: true });
    $.getScript('//connect.facebook.net/zh_TW/sdk.js', function(){
        FB.init({
          appId      : '927064760666737',
          xfbml      : true,
          version    : 'v2.3'
        });   
        FB.getLoginStatus(updateStatusCallback);
    });
    
    $("#login-fb").click(function(evt){
        evt.preventDefault();
        FB.login(function(response){
            if (response.status === 'connected') {
                // Logged into your app and Facebook.
                //$("#getLoginedUserProfile").show();
                //$("#login").hide();
                //getLoginedAuthInfo(response);
            } else if (response.status === 'not_authorized') {
                // The person is logged into Facebook, but not your app.
            } else {
                // The person is not logged into Facebook, so we're not sure if
                // they are logged into this app or not.
            }
        },{scope: 'public_profile,email'});
    });
});