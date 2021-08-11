<?php
use phpformbuilder\Form;
use fileuploader\server\FileUploader;
use phpformbuilder\Validator\Validator;
use phpformbuilder\database\Mysql;
use common\Utils;
use secure\Secure;

/* =============================================
    validation if posted
============================================= */
include_once CLASS_DIR . 'phpformbuilder/plugins/fileuploader/server/class.fileuploader.php';
include 'social_function.php';
if ($_SERVER["REQUEST_METHOD"] == "POST" && Form::testToken('form-create-leads') === true) {
    include_once CLASS_DIR . 'phpformbuilder/Validator/Validator.php';
    include_once CLASS_DIR . 'phpformbuilder/Validator/Exception.php';
    $validator = new Validator($_POST);   
    $validator->required()->validate('Name');
    $validator->maxLength(300)->validate('Name');    
    $validator->maxLength(150)->validate('Email');
    $validator->maxLength(150)->validate('Website');
    $validator->maxLength(50)->validate('Phonenumber');
    $validator->required()->validate('status');
    $validator->maxLength(20)->validate('status');    
    $validator->maxLength(150)->validate('source');

    // check for errors
    if ($validator->hasErrors()) {
        $_SESSION['errors']['form-create-leads'] = $validator->getAllErrors();
    } else {
        require_once CLASS_DIR . 'phpformbuilder/database/db-connect.php';
        require_once CLASS_DIR . 'phpformbuilder/database/Mysql.php';
        $db = new Mysql();
        $total_reach = str_replace(',', '',$_POST['FacebookReach'])+str_replace(',', '',$_POST['TwitterReach'])+str_replace(',', '',$_POST['InstagramReach'])+str_replace(',', '',$_POST['YouTubeReach'])+str_replace(',', '',$_POST['PinterestReach'])+str_replace(',', '',$_POST['Tiktokreach'])+str_replace(',', '',$_POST['PodcastsReach']);	        
        $tags1 ='';
    	  $tags = json_decode($_POST['tags']);    	 
    	  foreach($tags as $tag)
    	  {
    	    $tags1 .= $tag->value.',';
    	  }
    	  $tags1 = rtrim($tags1, ',');
        $insert['Name'] = Mysql::SQLValue($_POST['Name'], Mysql::SQLVALUE_TEXT);
        $insert['Total_reach'] = Mysql::SQLValue($total_reach, Mysql::SQLVALUE_NUMBER);        
        $insert['Description'] = Mysql::SQLValue($_POST['Description'], Mysql::SQLVALUE_TEXT);        
        $insert['Website'] = Mysql::SQLValue($_POST['Website'], Mysql::SQLVALUE_TEXT);
        $insert['Phonenumber'] = Mysql::SQLValue($_POST['Phonenumber'], Mysql::SQLVALUE_TEXT);
        $insert['status'] = Mysql::SQLValue($_POST['status'], Mysql::SQLVALUE_TEXT);
        $insert['assigned'] = Mysql::SQLValue($_POST['assigned'], Mysql::SQLVALUE_TEXT);
        $insert['AverageFee'] = Mysql::SQLValue($_POST['AverageFee'], Mysql::SQLVALUE_TEXT);
        $insert['TargetAudience'] = Mysql::SQLValue($_POST['TargetAudience'], Mysql::SQLVALUE_TEXT);
        $insert['Age'] = Mysql::SQLValue($_POST['Age'], Mysql::SQLVALUE_TEXT);
        $insert['FacebookPageURL'] = Mysql::SQLValue($_POST['FacebookPageURL'], Mysql::SQLVALUE_TEXT);
        $insert['FacebookReach'] = Mysql::SQLValue(str_replace(',', '',$_POST['FacebookReach']), Mysql::SQLVALUE_TEXT);
        $insert['TwitterURL'] = Mysql::SQLValue($_POST['TwitterURL'], Mysql::SQLVALUE_TEXT);
        $insert['TwitterReach'] = Mysql::SQLValue( str_replace(',', '',$_POST['TwitterReach']), Mysql::SQLVALUE_TEXT);
        $insert['InstagramURL'] = Mysql::SQLValue($_POST['InstagramURL'], Mysql::SQLVALUE_TEXT);
        $insert['InstagramReach'] = Mysql::SQLValue(str_replace(',', '',$_POST['InstagramReach']), Mysql::SQLVALUE_TEXT);
        $insert['YouTubeChannelURL'] = Mysql::SQLValue($_POST['YouTubeChannelURL'], Mysql::SQLVALUE_TEXT);
        $insert['YouTubeReach'] = Mysql::SQLValue(str_replace(',', '',$_POST['YouTubeReach']), Mysql::SQLVALUE_TEXT);
        $insert['PinterestURL'] = Mysql::SQLValue($_POST['PinterestURL'], Mysql::SQLVALUE_TEXT);
        $insert['PinterestReach'] = Mysql::SQLValue(str_replace(',', '',$_POST['PinterestReach']), Mysql::SQLVALUE_TEXT);
        $insert['Tiktokurl'] = Mysql::SQLValue($_POST['Tiktokurl'], Mysql::SQLVALUE_TEXT);
        $insert['Tiktokreach'] = Mysql::SQLValue(str_replace(',', '',$_POST['Tiktokreach']), Mysql::SQLVALUE_TEXT);
		$insert['Tiktokurl'] = Mysql::SQLValue($_POST['Tiktokurl'], Mysql::SQLVALUE_TEXT);
        $insert['Tiktokreach'] = Mysql::SQLValue(str_replace(',', '',$_POST['Tiktokreach']), Mysql::SQLVALUE_TEXT);
        $insert['PodcastsUrl'] = Mysql::SQLValue($_POST['PodcastsUrl'], Mysql::SQLVALUE_TEXT);
        $insert['PodcastsReach'] = Mysql::SQLValue(str_replace(',', '',$_POST['PodcastsReach']), Mysql::SQLVALUE_TEXT);
        $insert['tags'] = Mysql::SQLValue($tags1, Mysql::SQLVALUE_TEXT);
        $insert['source'] = Mysql::SQLValue($_POST['source'], Mysql::SQLVALUE_TEXT);
        $insert['notes'] = Mysql::SQLValue($_POST['notes'], Mysql::SQLVALUE_TEXT);
        $insert['last_updated'] = Mysql::SQLValue(date("Y-m-d H:i:s"), Mysql::SQLVALUE_TEXT);
        if (isset($_POST['Profile_pic']) && !empty($_POST['Profile_pic'])) {
            $posted_img = FileUploader::getPostedFiles($_POST['Profile_pic']);
            $Profile_pic = BASE_URL.'profile/'.$posted_img[0]['file'];
            $insert['Profile_pic'] = Mysql::SQLValue($Profile_pic, Mysql::SQLVALUE_TEXT);
        } else {
		        $image_url=''; 
		        $fburl = $_POST['FacebookPageURL'];
		        if($fburl !='' and $fburl !='N/A')
		        {
		        	$fuser = get_user($fburl);
		        	$image_url = 'https://graph.facebook.com/'.$fuser.'/picture?type=large';
		        }      
		         
			     $insert['Profile_pic'] = Mysql::SQLValue($image_url, Mysql::SQLVALUE_TEXT);
            }
        $db->throwExceptions = true;
        try {
            // begin transaction
            $db->transactionBegin();

            // insert new leads

            if (DEMO !== true && !$db->insertRow('leads', $insert)) {
                $error = $db->error();
                $db->transactionRollback();
                throw new \Exception($error);
            } else {
                $leads_last_insert_ID = $db->getLastInsertID();
                if (!isset($_POST['Profile_pic']))
                {
                $iurl = $_POST["InstagramURL"];
				      if($iurl !='' and $iurl !='N/A')
				      {
				      $iuser = get_user($iurl);
                ?>
                <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
                <script src="https://influencers.87am.agency/jquery.instagramFeed.min.js"></script>
                <script type="text/javascript">			
			    (function($){        
			            $.instagramFeed({
			                'username': '<?php echo $iuser;?>',
			                'get_data': true,
			                'callback': function(data){
			                  var follow = data['edge_followed_by']['count'];
			                  var profile = data['profile_pic_url'];
			                  console.log(data['profile_pic_url']);
			                  $.ajax({
								        url: 'https://influencers.87am.agency/admin/social_function.php?fun=updateinsta',
								        type: 'POST',
								        data: {								          
								          id:<?php echo $leads_last_insert_ID;?>,
								          followers:0,
								          profile:profile
								          },      
								        success: function(data) { 
								        console.log(data);
								      },
									   error: function (jqXHR, exception) {
		 								console.log('wrong!');
      								}
									  });	
			                 
			                }
			            });
			        
			    })(jQuery);
		</script>
		<?php
	   }
	   }
                if (!isset($error)) {
                    // ALL OK - NO DB ERROR
                    $db->transactionEnd();
                    $_SESSION['msg'] = Utils::alert(INSERT_SUCCESS_MESSAGE, 'alert-success has-icon');

                    // reset form values
                    Form::clear('form-create-leads');

                    // redirect to list page
                    if (isset($_SESSION['active_list_url'])) {
                        header('Location:' . $_SESSION['active_list_url']);
                    } else {
                        header('Location:https://influencers.87am.agency/admin/leads');
                    }

                    // if we don't exit here, $_SESSION['msg'] will be unset
                    exit();
                }
            }
        } catch (\Exception $e) {
            $msg_content = DB_ERROR;
            if (ENVIRONMENT == 'development') {
                $msg_content .= '<br>' . $e->getMessage() . '<br>' . $db->getLastSql();
            }
            $_SESSION['msg'] = Utils::alert($msg_content, 'alert-danger has-icon');
        }
    } // END else
} // END if POST
Form::clear('form-create-leads');
$form = new Form('form-create-leads', 'horizontal', 'novalidate', 'bs4');
$form->setAction('/admin/leads/create');
$form->startFieldset();
$db = new Mysql();
$qry1 ="select id,name from  phpcg_users";
$db->query($qry1);
$form->startFieldset('social information');
$form->groupInputs('FacebookPageURL', 'FacebookReach');
$form->setCols(2, 6);
// FacebookPageURL --
$form->addInput('text', 'FacebookPageURL', '', 'FacebookPageURL', '');

// FacebookReach --
$form->setCols(2, 2);
$form->addInput('text', 'FacebookReach', '', 'FacebookReach', '');

$form->groupInputs('TwitterURL', 'TwitterReach');
$form->setCols(2, 6);
// TwitterURL --
$form->addInput('text', 'TwitterURL', '', 'TwitterURL', '');
$form->setCols(2, 2);
// TwitterReach --
$form->addInput('text', 'TwitterReach', '', 'TwitterReach', '');


$form->groupInputs('InstagramURL', 'InstagramReach');
$form->setCols(2, 6);
// InstagramURL --
$form->addInput('text', 'InstagramURL', '', 'InstagramURL', '');
$form->setCols(2, 2);
// InstagramReach --
$form->addInput('text', 'InstagramReach', '', 'InstagramReach', '');

$form->groupInputs('Tiktokurl', 'Tiktokreach');
$form->setCols(2, 6);
// Tiktokurl --
$form->addInput('text', 'Tiktokurl', '', 'TikTokURL', '');
$form->setCols(2, 2);
// Tiktokurl --
$form->addInput('text', 'Tiktokreach', '', 'TikTokReach', '');


$form->groupInputs('PodcastsUrl', 'PodcastsReach');
$form->setCols(2, 6);
// Tiktokurl --
$form->addInput('text', 'PodcastsUrl', '', 'PodcastsURL', '');
$form->setCols(2, 2);
// Tiktokurl --
$form->addInput('text', 'PodcastsReach', '', 'PodcastsReach', '');


$form->groupInputs('YouTubeChannelURL', 'YouTubeReach');
$form->setCols(2, 6);
// YouTubeChannelURL --
$form->addInput('text', 'YouTubeChannelURL', '', 'YouTubeChannelURL', '');
$form->setCols(2, 2);
// YouTubeReach --
$form->addInput('text', 'YouTubeReach', '', 'YouTubeReach', '');


$form->groupInputs('PinterestURL', 'PinterestReach');
$form->setCols(2, 6);
// PinterestURL --
$form->addInput('text', 'PinterestURL', '', 'PinterestURL', '');
$form->setCols(2, 2);
// PinterestReach --
$form->addInput('text', 'PinterestReach', '', 'PinterestReach', '');

$form->endFieldset();

$form->startFieldset('Contact Information');

// Name --
$form->setCols(2, 10);
$form->addInput('text', 'Name', '', 'Name', 'required');

// status --
//$form->addInput('text', 'status', '', 'Status', 'required');
$form->addOption('status', 'Inactive', 'Inactive');
$form->addOption('status', 'Active', 'Active');
$form->addSelect('status', 'Status', 'class=select2 form-control, data-width=100%','required');
// Profile_pic --
$fileUpload_config = array(
'xml'           => 'image-upload', // the thumbs directories must exist
'uploader'      => 'ajax_upload_file.php', // the uploader file in phpformbuilder/plugins/fileuploader/[xml]/php
'upload_dir'    => ROOT.'profile/', // the directory to upload the files. relative to [plugins dir]/fileuploader/[xml]/php/[uploader]
'limit'         => 1, // max. number of files
'file_max_size' => 5, // each file's maximal size in MB {null, Number}
'extensions'    => ['jpg', 'jpeg', 'png'],
'thumbnails'    => false,
'editor'        => false,
'width'         => 9999,
'height'        => 9999,
'crop'          => false,
'debug'         => true
);
$form->addFileUpload('file', 'Profile_pic', '', 'Profile Pic', '', $fileUpload_config);
// assigned --
while (!$db->endOfSeek()) {
$row = $db->row();
$form->addOption('assigned', $row->id,$row->name);
}
$form->addSelect('assigned', 'Assigned', 'class=select2 form-control, data-width=100%','required');

// Description --
$form->addInput('text', 'Description', '', 'Description', '');

// source --
$qry2 ="select location from  location";
$db->query($qry2);
while (!$db->endOfSeek()) {
$row = $db->row();
$form->addOption('source',$row->location,$row->location);
}
$form->addSelect('source', 'Location', 'class=select2 form-control, data-width=100%','required');

// Email --
$form->addInput('text', 'Email', '', 'Email', '');

// Phonenumber --
$form->addInput('text', 'Phonenumber', '', 'Phonenumber', '');

// Website --
$form->addInput('text', 'Website', '', 'Website', '');

// AverageFee --
$form->addInput('text', 'AverageFee', '', 'AverageFee', '');

// TargetAudience --
$form->addInput('text', 'TargetAudience', '', 'TargetAudience', '');

// Age --
$form->addInput('text', 'Age', '', 'Age', '');

// tags --
$form->addInput('text', 'tags', '', 'Tags', '');

// notes --
$form->setCols(2, 10);
$form->addInput('text', 'notes', '', 'Notes', '');



$form->addBtn('button', 'cancel', 0, '<i class="' . ICON_BACK . ' position-left"></i>' . CANCEL, 'class=btn btn-warning ladda-button legitRipple, onclick=history.go(-1)', 'btn-group');
$form->addBtn('submit', 'submit-btn', 1, SUBMIT . '<i class="' . ICON_CHECKMARK . ' position-right"></i>', 'class=btn btn-success ladda-button legitRipple', 'btn-group');
$form->setCols(0, 12);
$form->centerButtons(true);
$form->printBtnGroup('btn-group');
$form->endFieldset();
$form->addPlugin('nice-check', 'form', 'default', array('%skin%' => 'green'));
?>
<style type="text/css">
.sidebar-category:nth-child(2) {
    display: none!important; 
}
label[for=FacebookPageURL]
{
    background-image:url('https://influencers.87am.agency/img/facebook-16.svg');
}
label[for=TwitterURL]
{
    background-image:url('https://influencers.87am.agency/img/twitter-16.svg');
}
label[for=InstagramURL]
{
    background-image:url('https://influencers.87am.agency/img/instagram-16.svg');
}
label[for=Tiktokurl]
{
    background-image:url('https://influencers.87am.agency/img/tik-tok.svg');
}
label[for=YouTubeChannelURL]
{
    background-image:url('https://influencers.87am.agency/img/youtube-16.svg');
}
label[for=PinterestURL]
{
    background-image:url('https://influencers.87am.agency/img/pinterest-16.svg');
}
label[for=PodcastsUrl]
{
    background-image:url('https://influencers.87am.agency/img/podcasts.svg');
}
.form-group label
{
    background-repeat: no-repeat;
    background-position: left center;
    background-size: 10%;
    padding-left: 25px;
}
fieldset
{
    margin-left: 8px!important;
}
label:not(.form-check-label) {
    padding-right: 1rem!important;
}
</style>
