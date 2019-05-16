<?php
/**
 * selfDestruct
 * https://github.com/mwynwood/selfDestruct
 * 
 * Create messages that have a limited lifespan before they self descruct!
 * Put together by Marcus Wynwood, 16 May 2019
 * Using the "my_simple_crypt" function by Nazmul Ahsan
 **/


/**
 * Encrypt and decrypt
 * 
 * @author Nazmul Ahsan <n.mukto@gmail.com>
 * @link http://nazmulahsan.me/simple-two-way-function-encrypt-decrypt-string/
 *
 * @param string $string string to be encrypted/decrypted
 * @param string $action what to do with this? e for encrypt, d for decrypt
 */
function my_simple_crypt( $string, $action = 'e' ) {
    // you may change these values to your own
    $secret_key = 'my_simple_secret_key';
    $secret_iv = 'my_simple_secret_iv';

    $output = false;
    $encrypt_method = "AES-256-CBC";
    $key = hash( 'sha256', $secret_key );
    $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

    if( $action == 'e' ) {
        $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
    }
    else if( $action == 'd' ){
        $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
    }

    return $output;
}

date_default_timezone_set("Australia/Hobart");
$allowedHTML = "<h1><h2><h3><p><hr><ul><ol><li><strong><em>";
$maxAge = 86400; // 86400 seconds is 24 hours

if (isset($_GET['page'])) {
    $encrypted = $_GET['page'];
    $decrypted = my_simple_crypt( $encrypted, 'd' );
    //echo "<a href='" . basename($_SERVER['REQUEST_URI'])  . "'>" . date("l jS \of F Y h:i:s A", strtok($decrypted, "\n")) . "</a>";
    
    $age = date("U") - date("U", strtok($decrypted, "\n"));
    $boomDate = date("l jS \of F Y h:i:s A", (strtok($decrypted, "\n") + $maxAge) );
    
    if($age >= $maxAge) {
        echo "<h1>This page has self destructed!</h1>";
    } else {
        echo "<p>This page will self destruct on " . $boomDate;
        echo "<hr>" . preg_replace('/^.+\n/', '', $decrypted) ."";
    }
}

if (isset($_GET['text'])) {
    $text = date("U") . "\n" . preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', strip_tags($_GET['text'], $allowedHTML));
    $encrypted = my_simple_crypt( $text, 'e' );
    header("Location: ?page=".$encrypted);
}

if (!isset($_GET['text']) && !isset($_GET['page'])) {
?>
<form action="" method="get">
    <label for="text">Enter your text:</label><br>
    <textarea id="text" name="text" rows="5" cols="40" maxlength='200'></textarea><br>
    <p>Length: <span id="count">0/200</span></p>
    <p>You can use: <?php echo htmlspecialchars($allowedHTML); ?></p>
    <input type="submit" value="Submit">
</form>
<script>
    var textarea = document.querySelector("textarea");
    textarea.addEventListener("input", function(){
        var maxlength = this.getAttribute("maxlength");
        var currentLength = this.value.length;
        document.getElementById('count').innerHTML = currentLength + "/" + maxlength;
    });
</script>
<?php } ?>
