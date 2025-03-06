<?
include('simple_html_dom.php');


$uagent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";
$req_url = 'https://rostov.hh.ru/search/vacancy?saved_search_id=83706445&no_magic=true&date_from=05.03.2025+10%3A40%3A57&area=113&employment_form=PART&employment_form=PROJECT&search_field=name&search_field=company_name&search_field=description&work_format=REMOTE&text=%D0%B2%D0%B5%D1%80%D1%81%D1%82%D0%B0%D0%BB%D1%8C%D1%89%D0%B8%D0%BA+OR+web+%D1%80%D0%B0%D0%B7%D1%80%D0%B0%D0%B1%D0%BE%D1%82%D1%87%D0%B8%D0%BA&enable_snippets=true';
$ch = curl_init($req_url);
curl_setopt($ch, CURLOPT_URL, $req_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_ENCODING, "");
curl_setopt($ch, CURLOPT_USERAGENT, $uagent);  // useragent
curl_setopt($ch, CURLOPT_TIMEOUT, 120);
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$err = curl_errno($ch);
$errmsg = curl_error($ch);
$header = curl_getinfo($ch);
curl_close($ch);

echo "err:" . ($err == CURLE_OK);
echo "https:" . $header['http_code'];


if ($err == CURLE_OK && $header['http_code'] == 200) {
    /*	header('Content-type: '.$header['content_type']);
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
        header("Pragma: no-cache");
    */

    $recount = $header['redirect_count'];
    while ($recount-- > 0)
        $response = substr($response, strpos($response, "\r\n\r\n") + 4);

    $splitter = strpos($response, "\r\n\r\n");
    $raw_headers = substr($response, 0, $splitter);
    $content = substr($response, $splitter + 4);

    echo "ct:" . $header['content_type'];
    // echo "content".$content;

    if ($header['content_type'] == null || strpos($header['content_type'], 'text/html') !== FALSE || strpos($header['content_type'], 'text/xml') !== FALSE) {
        //	if(false){
        // $html = str_get_html($content);
        // $html = str_get_html('<body><p>Hi</p></body>');
        $html = new simple_html_dom();
        $html->load($content);
        // echo "html:";
        // var_dump($html);

        if ($html !== false) {
            foreach($html->find('[class^=vacancy-info]') as $node) {
                echo "<br><br>";
                echo $node->plaintext;
                echo "<br><br>";
            }
        }
        ;
    }
    ;
}
?>