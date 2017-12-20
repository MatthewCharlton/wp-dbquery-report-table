jQuery(document).ready(function ($) {

    var successNotice = '<div class="notice notice-success is-dismissible wpdbqrt_admin_notice">';
    var errorNotice = '<div class="notice notice-error is-dismissible wpdbqrt_admin_notice">';

    $("#dbquery_report_table_head_form .update-btn").click(function (e) {
        e.preventDefault();
        var table_head_content = $("#dbquery_report_table_head_form textarea").val();
        var table_head_content_nonce = $("#dbquery_report_table_head_form input[name='dbquery_report_table_head_update_query_nonce']").val();
        var th_report_table_id = $("#dbquery_report_table_head_form input[name='dbquery_report_table_id']").val();
        var th_data = {
            action: 'report_table_head_update_query',
            dbquery_report_table_id: th_report_table_id,
            dbquery_report_table_head_update_query_nonce: table_head_content_nonce,
            dbquery_report_table_head_content: table_head_content
        };
        $.post(the_ajax_script.ajaxurl, th_data, function (response) {
            if(response != 1){
                $("#wpbody-content").prepend(errorNotice + '<p>Table head HTML has not been updated!</p></div>');
                setTimeout(function(){
                    $(".notice").slideUp();
                }, 3000); 
            } else {
                $("#wpbody-content").prepend(successNotice + '<p>Table head HTML has been updated!</p></div>');
                setTimeout(function(){
                    $(".notice").slideUp();
                }, 3000); 
            }
        });
        return false;
    });

    $("#dbquery_report_table_query_form .update-btn").click(function (e) {
        e.preventDefault();
        var db_query = $("#dbquery_report_table_query_form textarea").val();
        var dbquery_update_query_nonce = $("#dbquery_report_table_query_form input[name='dbquery_report_dbquery_update_query_nonce']").val();
        var query_report_table_id = $("#dbquery_report_table_head_form input[name='dbquery_report_table_id']").val();
        var query_data = {
            action: 'dbquery_report_dbquery_update_query',
            dbquery_report_table_id: query_report_table_id,
            dbquery_report_dbquery_update_query_nonce: dbquery_update_query_nonce,
            dbquery_report_table_query: db_query
        };
        $.post(the_ajax_script.ajaxurl, query_data, function (response) {
            if(response != 1){
                $("#wpbody-content").prepend(errorNotice + '<p>DB query has not been updated!</p></div>');
                setTimeout(function(){
                    $(".notice").slideUp();
                }, 3000); 
            } else {
                $("#wpbody-content").prepend(successNotice + '<p>DB query has been updated!</p></div>');
                setTimeout(function(){
                    $(".notice").slideUp();
                }, 3000); 
            }
        });
        return false;
    });

});

function confirmDelete(id) {
    var confirmed = confirm('Are you sure you want to delete DBQuery Report Table ' + id + '?');
    return (confirmed) ? true : false;
}