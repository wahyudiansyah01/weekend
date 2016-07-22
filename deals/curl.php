 <?php 


$post = [
    'email' => $_POST["email"],
    'name' => $_POST["name"],
];

$ch = curl_init('https://app.mailjet.com/account/tools/widget/subscribe/1N5');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

// execute!
$response = curl_exec($ch);

// close the connection, release resources used
curl_close($ch);

// do anything you want with your response
var_dump($response);
?>