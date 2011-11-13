/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
//jQuery(document).ready(function($) {
//    
//    var urlArr = new Array();
//    var url = '',
//        name = '';
//
//    $("#add-submit").click(function () {
//         url = $(".add-feed-input").val();
//         name = $(".add-name-input").val();
//         $("table#list-rss").last().append("<tr><td>" +name+ "</td><td>" +url+ "</td></tr>");
//    });    
//    
//
//    
//$('#save-rss').click(function(){
//
//
//      var urls = $(".add-feed-input").serializeArray();
//
//
//    var data = {
//            action: 'hypernews_set_group',
//            address: urls
//        };
//
//        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
//        jQuery.post(hypernewsObj.ajaxurl, data, function(response) {
//
//            console.log(response);
//        });
//    });
//
//});