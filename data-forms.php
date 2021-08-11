<?php
use secure\Secure;
use crud\ElementsFilters;
use crud\Elements;
use phpformbuilder\Form;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;

session_start();

include_once '../conf/conf.php';
include_once ADMIN_DIR . 'secure/class/secure/Secure.php';
include_once CLASS_DIR . 'phpformbuilder/Form.php';

// $item    = lowercase compact table name
$item = $match['params']['item'];

// create|edit|delete
$action = $match['params']['action'];

$canonical = ADMIN_URL . $item . '/' . $action;

if ($action == 'edit' || $action == 'delete') {
    // primary key = record to edit
    $pk         = $match['params']['primary_key'];
    $canonical .= '/' . $pk;
}

$element    = new Elements($item);
$table      = $element->table;
$item_class = $element->item_class;

// lock page
if ($action == 'edit' && Secure::canUpdate($table) !== true && Secure::canUpdateRestricted($table) !== true) {
    Secure::logout();
} elseif (($action == 'create' || $action == 'delete') && (Secure::canCreate($table) !== true && Secure::canCreateRestricted($table) !== true)) {
    Secure::logout();
}

// info label
$info_label       = '';
$info_label_class = '';
if ($action == 'create') {
    $info_label       = ADD_NEW;
    $info_label_class = 'primary';
    $desc             = $info_label . ' ' . $table;
} elseif ($action == 'edit') {
    $info_label       = EDIT;
    $info_label_class = 'warning';
    $desc             = $info_label . ' ' . $table . ' ' . $pk;
} elseif ($action == 'delete') {
    $info_label       = DELETE_ACTION;
    $info_label_class = 'danger';
    $desc             = $info_label . ' ' . $table . ' ' . $pk;
}

// sidebar
include_once 'inc/sidebar.php';

require_once ROOT . 'vendor/autoload.php';

$loader = new Twig_Loader_Filesystem('templates');
$twig   = new Twig_Environment($loader, array(
    'debug' => DEBUG,
));
include_once ROOT . 'vendor/twig/twig/lib/Twig/Extension/CrudTwigExtension.php';
$twig->addExtension(new CrudTwigExtension());
if (ENVIRONMENT == 'development') {
    $twig->addExtension(new Twig_Extension_Debug());
}
$template_sidebar = $twig->loadTemplate('sidebar.html');
$template_js      = $twig->loadTemplate('data-forms-js.html');

if (!file_exists('inc/forms/' . $item . '-' . $action . '.php')) {
    exit('inc/forms/' . $item . '-' . $action . '.php : ' . ERROR_FILE_NOT_FOUND);
}

include_once 'inc/forms/' . $item . '-' . $action . '.php';
$form->useLoadJs('core');
$form->setMode('development');

$msg = '';
if (isset($_SESSION['msg'])) {
    // catch registered message & reset.
    $msg = $_SESSION['msg'];
    unset($_SESSION['msg']);
}

$back_url = ADMIN_URL . $item;
if (isset($_SESSION['active_list_url'])) {
    $back_url = $_SESSION['active_list_url'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title><?php echo SITENAME . ' ' . ADMIN . ' - ' . $desc; ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="canonical" href="<?php echo  $canonical; ?>" />
    <meta name="description" content="<?php echo SITENAME; ?> - Bootstrap admin panel - CRUD PHP MySQL - <?php echo $desc; ?>.">
    <?php
        include_once 'inc/css-includes.php';
    ?>
</head>

<body class="<?php echo DEFAULT_BODY_CLASS; ?>">
    <?php include_once 'inc/header.php'; ?>
    <div class="page-container admin-form">
        <?php echo $template_sidebar->render(array('sidebar' => $sidebar)); ?>
        <div class="content-wrapper">
            <div id="msg"><?php echo $msg; ?></div>
            <div class="row">
                <div class="col">
                    <div class="card <?php echo DEFAULT_CARD_CLASS; ?>">
                        <div class="card-header <?php echo DEFAULT_CARD_HEADING_CLASS; ?>">
                            <p class="text-semibold no-margin"><a href="<?php echo $back_url; ?>"><i class="<?php echo ICON_BACK; ?> position-left"></i></a>Add/Edit Influencer</p>
                            <div class="heading-elements">
                                <span class="label label-<?php echo $info_label_class; ?> mt-5"><?php echo $info_label; ?></span>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php
                            $form->render();
                            /*echo '$item : ' . $item . '<br>';
                            echo '$action : ' . $action . '<br>';
                            if($action == 'edit' || $action == 'delete') {
                                echo '$pk : ' . $pk . '<br>';
                            }*/
                            ?>
                        </div> <!-- end card body -->
                    </div> <!-- end card -->
                </div> <!-- end col -->
            </div> <!-- end row -->
        </div> <!-- end content-wrapper -->
    </div> <!-- end container -->
    <?php
        include_once 'inc/js-includes.php';
        $form->printJsCode('core');
        echo $template_js->render(array('object' => ''));

        // load form javascript if exists
    if (file_exists('inc/forms/' . $item . '.js')) {
        ?>
    <script type="text/javascript" src="<?php echo ADMIN_URL . 'inc/forms/' . $item . '.js'; ?>"></script>
    <?php
    }
    ?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://influencers.87am.agency/jquery.instagramFeed.min.js"></script>
<script type="text/javascript" >
$("#FacebookPageURL").blur(function() {	
var info_id = 'FacebookPageURL';    
var link11 = document.getElementById('FacebookPageURL').value;		
	 link11 = stipTrailingSlash(link11);							 
var link2 = link11.split( '/' );		 
var user = link2[link2.length - 1];
var token = '485132045731960|VZv60FrXT01l8esQNpNwhEiiEBU';
		
      $.ajax({
        url: 'https://graph.facebook.com/'+user,
        dataType: 'json',
        type: 'GET',
        data: {
          access_token:token,
          fields:'fan_count,name,about,emails,website,location'
        },
        success: function(data) { 
            console.log(JSON.stringify(data)); 
		  
		  
		if(data.fan_count && data.fan_count != 'undefined'){
          var followers = parseInt(data.fan_count);
          var k = followers;          
		 document.getElementById('FacebookReach').value = addCommas(k);	
		  if(data.name && data.name =="undefined")
          $("#Name").val("");
        else
          $("#Name").val(data.name);
          
        if(data.description && data.description =="undefined")
          document.getElementById("Description").value = "";
        else
          document.getElementById("Description").value = data.about;

        if(data.website && data.website =="undefined")
          document.getElementById("Website").value = "";
        else
          document.getElementById("Website").value = data.website;

        if(data.emails && data.emails =="undefined")
          document.getElementById("Email").value = "";
        else
          document.getElementById("Email").value = data.emails;
		 
		 
        }else if(data.fan_count && data.fan_count == 'undefined'){
		 document.getElementById('FacebookReach').value = '';			
		}
		token_msg('hide' ,info_id, '');
      },
	  
    error: function (jqXHR, exception) {
        
       
		 token_msg('show' ,info_id, 'Invalid Link.');
		 
    },
	  
	  }); 
 
});
function token_msg(action ,id, msg){
		var id = ''+id+'';
		if(action == 'show'){
		 if(!$("#"+id).parent().hasClass("have_error")) {	
		 $("#"+id).parent().addClass('have_error');	
		 $("#"+id).parent().append('<div class="error">'+msg+'<div>'); 
		 }
		}
		else	if(action == 'hide'){
		 $(".error").hide();
		}
		
}

function stipTrailingSlash(str){
	   if(str.charAt(str.length-1) == "/"){ str = str.substr(0, str.length - 1);}
	   return str;
}
$("#TwitterURL").blur(function() {
var link11 = document.getElementById('TwitterURL').value;		
	 link11 = stipTrailingSlash(link11);							 
var link2 = link11.split( '/' );		 
var twitter_user = link2[link2.length - 1];
 $.ajax({
        url: 'https://influencers.87am.agency/admin/social_reach.php',
        dataType: 'json',
        type: 'GET',
        data: {
          user:twitter_user,
          social_type:'twitter'          
        },
        success: function(data) { 
           var followers = parseInt(data);  
		     document.getElementById('TwitterReach').value = addCommas(followers); 
		
      },
	  
    error: function (jqXHR, exception) {       
       
		 document.getElementById('TwitterReach').value = '';
		 
    },
	  
	  });	
});

$("#YouTubeChannelURL").blur(function() {
var info_id = 'YouTubeChannelURL';      
var link11 = document.getElementById('YouTubeChannelURL').value;		
	 link11 = stipTrailingSlash(link11);							 
var link2 = link11.split( '/' );		 
var user = link2[link2.length - 1];
$.ajax({
        url: 'https://www.googleapis.com/youtube/v3/channels',
        dataType: 'jsonp',
        type: 'GET',
        data:{
          part:'statistics',
          id:user,
          key: 'AIzaSyD2K-K2yfCXMAeJRzB3Y6Et2ki0ALFjpMc'
        },
        success: function(data) {
        
          if(data.items && data.items !='undefined' && data.items.length != 0){
          var subscribers = parseInt(data.items[0].statistics.subscriberCount);
		  document.getElementById('YouTubeReach').value = addCommas(subscribers); 
          }
          else
          {
          	/*for video*/
          	$.ajax({
        url: 'https://www.googleapis.com/youtube/v3/channels',
        dataType: 'jsonp',
        type: 'GET',
        data:{
          part:'statistics',
          forUsername:user,
          key: 'AIzaSyD2K-K2yfCXMAeJRzB3Y6Et2ki0ALFjpMc'
        },
        success: function(data) {
        
          if(data.items && data.items !='undefined' && data.items.length != 0){
          var subscribers = parseInt(data.items[0].statistics.subscriberCount);
		  document.getElementById('YouTubeReach').value = addCommas(subscribers); 
          }
          else
          {
          	console.log(data);
          	 if(user!='')
             token_msg('show' ,info_id, 'Invalid Link.'); 
          }
      },
	  
    error: function (jqXHR, exception) {       
       
		 document.getElementById('YouTubeReach').value = '';
		 
    },
	  
	  });
          	/*for video*/
          }
      },
	  
    error: function (jqXHR, exception) {       
       
		 document.getElementById('YouTubeReach').value = '';
		 
    },
	  
	  });

});

$("#PinterestURL").blur(function() {
var link11 = document.getElementById('PinterestURL').value;		
	 link11 = stipTrailingSlash(link11);							 
var link2 = link11.split( '/' );		 
var user = link2[link2.length - 1];

 $.ajax({
        url: 'https://api.pinterest.com/v3/pidgets/users/'+user+'/pins',
        dataType: 'jsonp',
        type: 'GET',      
        success: function(data) { 
        var followers = parseInt(data.data.user.follower_count);
		   document.getElementById('PinterestReach').value = addCommas(followers);
      },	  
      error: function (jqXHR, exception) {       
       
		 document.getElementById('PinterestReach').value = '';
		 
      },
	  
	  });	
});

/*$("#InstagramURL").blur(function() {
var info_id = 'InstagramURL';          
var link11 = document.getElementById('InstagramURL').value;		
	 link11 = stipTrailingSlash(link11);							 
var link2 = link11.split( '/' );		 
var user = link2[link2.length - 1];
var token ="3216680391.5a20414.d66b30dc0857473dbf9723d34b502a73";
$.ajax({
        url: 'https://influencers.87am.agency/admin/social_reach.php',
        // dataType: 'jsonp',
        type: 'GET',
        data: {
          user: user,
          social_type:'instagram'
        },      
        success: function(data) { 
        console.log(data);
        if(data !='null'){
        var followers = parseInt(data);;
		   document.getElementById('InstagramReach').value = addCommas(followers);
        }
        else{	
				
			 token_msg('show' ,info_id, 'Invalid  Link');
			}
      },	  
      error: function (jqXHR, exception) {       
       
		 document.getElementById('InstagramReach').value = '';
		 
      },
	  
	  });	
});*/
(function($){
        $("#InstagramURL").blur(function() {
        	var info_id = 'InstagramURL';          
			var link11 = document.getElementById('InstagramURL').value;		
				 link11 = stipTrailingSlash(link11);							 
			var link2 = link11.split( '/' );		 
			var user = link2[link2.length - 1];
			if(user == "?hl=en")
			user = link2[link2.length - 2];
            $.instagramFeed({
                'username': user,
                'get_data': true,
                'callback': function(data){
                	console.log(data['edge_followed_by']);                    
                if(data !='null'){
			        var followers = data['edge_followed_by']['count'];
					   document.getElementById('InstagramReach').value = addCommas(followers);
			        }
			        else{								
						 token_msg('show' ,info_id, 'Invalid  Link');
						}    
                }
            });
        });
})(jQuery);

function addCommas(nStr)
{
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}
$( document ).ready(function() {
   $('#FacebookReach').prop('readonly',true);
   $('#Twit terReach').prop('readonly',true);
   $('#InstagramReach').prop('readonly',true);
   $('#PinterestReach').prop('readonly',true);
   //$('#YouTubeReach').prop('readonly',true);
});
</script>   
<link rel="stylesheet" href="https://influencers.87am.agency/dist/tagify.css">
<script type="text/javascript" src="https://influencers.87am.agency/dist/jQuery.tagify.min.js"></script> 
<script src="https://influencers.87am.agency/dist/tagify.js"></script>
<?php 
$qry1 ="select tags from leads";
$db->query($qry1);
$users = $db->row();
$tag_options = array();
while (!$db->endOfSeek()) {
$row = $db->row();
$tags = explode(',',$row->tags);
$tag_options = array_unique (array_merge($tag_options,$tags));
}
$all_tags = '';
foreach($tag_options as $tag)
{
 $all_tags .= '"'.$tag.'",';   	   
}
$all_tags = rtrim($all_tags, ',');
?>
<script type="text/javascript" >
var input = document.querySelector('input[name="tags"]'),
    // init Tagify script on the above inputs
    tagify = new Tagify(input, {
      whitelist:[<?php echo $all_tags; ?>], 
      maxTags: 1000,
      dropdown: {
        maxItems: 1000,           // <- mixumum allowed rendered suggestions
        classname: "tags-look", // <- custom classname for this dropdown, so it could be targeted
        enabled: 0,             // <- show suggestions on focus
        closeOnSelect: false    // <- do not hide the suggestions dropdown once an item has been selected
      }
    })      
</script>
<style type="text/css">
.tags-look .tagify__dropdown__item{
  display: inline-block;
  border-radius: 3px;
  padding: .3em .5em;
  border: 1px solid #CCC;
  background: #F3F3F3;
  margin: .2em;
  font-size: .85em;
  color: black;
  transition: 0s;
}

.tags-look .tagify__dropdown__item--active{
  color: black;
}

.tags-look .tagify__dropdown__item:hover{
  background: lightyellow;
  border-color: gold;
}
.error
{
    color:red;
}
</style>
</body>

</html>
