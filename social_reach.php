<?php
if(isset($_GET['social_type']))
{
if($_GET['social_type']=='twitter')
{
$user = $_GET['user'];
$data = file_get_contents("https://cdn.syndication.twimg.com/widgets/followbutton/info.json?screen_names=$user");
$data = json_decode($data, true);
echo isset($data[0]['followers_count']) ? $data[0]['followers_count'] : json_decode(false);
}
}
if(isset($_GET['social_type']))
{
if($_GET['social_type']=='instagram')
{
$otherPage = $_GET['user']; 
$response = file_get_contents("https://www.instagram.com/$otherPage/?__a=1");
if ($response !== false) {
    $data = json_decode($response, true);
    if ($data !== null) {
        $follows = $data['graphql']['user']['edge_follow']['count'];
        $followedBy = $data['graphql']['user']['edge_followed_by']['count'];
        
    }
}

echo json_encode($followedBy);
}
}

if(isset($_GET['leadtype']))
{
$servername = "aabqflk4iga9gl.ckycshi92lef.us-west-2.rds.amazonaws.com";
$username = "influencer";
$password = "creative11";
$dbname = "influencer";
	
	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	$conn->set_charset("utf8");
	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}
	$id = $_GET['id']; 
	$sql = "SELECT leads.*,firstname FROM leads JOIN phpcg_users ON leads.assigned=phpcg_users.ID Where leads.id=".$id;
	$result = $conn->query($sql);
	$row = $result->fetch_assoc();
	$image_url = '';
	
   $image_url = $row['Profile_pic'];
   if($image_url == '' or is_numeric($image_url))
   $image_url = 'https://asset.telunjuk.co.id/s/images/default-user.png';
   
   $row['profile_img']=$image_url;
   if(isset($row['firstname']))
   $row['assigned'] = $row['firstname'];
   else 
   $row['assigned'] = '';
	if ($result->num_rows > 0) {
		echo $data = json_encode($row, JSON_UNESCAPED_UNICODE);
	}
}


?>
