<?php
namespace hitwpupdateserver\app\core;
class crypt {


  public static  function encrypt($data)
    {
        $first_key = base64_decode(FIRSTKEY);
        $second_key = base64_decode(SECONDKEY);

        $method = "aes-256-cbc";
        $iv_length = openssl_cipher_iv_length($method);
        $iv = openssl_random_pseudo_bytes($iv_length);

        $first_encrypted = openssl_encrypt($data,$method,$first_key, OPENSSL_RAW_DATA ,$iv);
        $second_encrypted = hash_hmac('sha3-512', $first_encrypted, $second_key, TRUE);

        $output = base64_encode($iv.$second_encrypted.$first_encrypted);
        return $output;
    }

   public static function decrypt($input)
    {
        $first_key = base64_decode(FIRSTKEY);
        $second_key = base64_decode(SECONDKEY);
        $mix = base64_decode($input);

        $method = "aes-256-cbc";
        $iv_length = openssl_cipher_iv_length($method);

        $iv = substr($mix,0,$iv_length);
        $second_encrypted = substr($mix,$iv_length,64);
        $first_encrypted = substr($mix,$iv_length+64);

        $data = openssl_decrypt($first_encrypted,$method,$first_key,OPENSSL_RAW_DATA,$iv);
        $second_encrypted_new = hash_hmac('sha3-512', $first_encrypted, $second_key, TRUE);

        if (hash_equals($second_encrypted,$second_encrypted_new))
            return $data;

        return false;
    }


    /**
     * Generiert neue Keys und speichert sie in einer Datei
     *
     * @param string $filepath Pfad zur Key-Datei (z.B. '/path/to/keys.php')
     * @return bool True bei Erfolg, False bei Fehler
     */
    public static function generateAndSaveKeys($filepath)
    {
        $firstKey = base64_encode(openssl_random_pseudo_bytes(32));
        $secondKey = base64_encode(openssl_random_pseudo_bytes(64));

        // Erstelle PHP-Datei mit den Keys
        $content = "<?php\n";
        $content .= "// Automatisch generierte Verschlüsselungs-Keys\n";
        $content .= "// Erstellt am: " . date('Y-m-d H:i:s') . "\n";
        $content .= "define('FIRSTKEY', '" . $firstKey . "');\n";
        $content .= "define('SECONDKEY', '" . $secondKey . "');\n";

        // Speichere die Datei
        $result = file_put_contents($filepath, $content);

        if ($result !== false) {
            // Setze restriktive Berechtigungen (nur Owner kann lesen/schreiben)
            chmod($filepath, 0600);
            return true;
        }

        return false;
    }

    /**
     * Lädt die Keys aus einer Datei und definiert die Konstanten
     *
     * @param string $filepath Pfad zur Key-Datei
     * @return bool True bei Erfolg, False bei Fehler
     */
    public static function loadKeys($filepath)
    {
        if (!file_exists($filepath)) {
            return false;
        }

        // Prüfe, ob Konstanten bereits definiert sind
        if (defined('FIRSTKEY') || defined('SECONDKEY')) {
            return true; // Keys bereits geladen
        }

        // Lade die Key-Datei
        require_once $filepath;

        // Prüfe, ob die Konstanten nun definiert sind
        return defined('FIRSTKEY') && defined('SECONDKEY');
    }


    /*
    public static function newkey(){
      echo base64_encode(openssl_random_pseudo_bytes(32)).'</br>';
      echo base64_encode(openssl_random_pseudo_bytes(64));
    } */

}