<?
$subcats = ['WordPress', 'Верстка', 'Лендинги', 'Laravel'];

include('simple_html_dom.php');

function get_html($req_url): simple_html_dom|null
{

    $uagent = "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322)";
    // $req_url = 'https://rostov.hh.ru/search/vacancy?saved_search_id=83706445&no_magic=true&date_from=05.03.2025+10%3A40%3A57&area=113&employment_form=PART&employment_form=PROJECT&search_field=name&search_field=company_name&search_field=description&work_format=REMOTE&text=%D0%B2%D0%B5%D1%80%D1%81%D1%82%D0%B0%D0%BB%D1%8C%D1%89%D0%B8%D0%BA+OR+web+%D1%80%D0%B0%D0%B7%D1%80%D0%B0%D0%B1%D0%BE%D1%82%D1%87%D0%B8%D0%BA&enable_snippets=true';
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

    // echo "err:" . ($err == CURLE_OK);
    // echo "https:" . $header['http_code'];


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

        // echo "ct:" . $header['content_type'];
        // echo "content".$content;

        if ($header['content_type'] == null || strpos($header['content_type'], 'text/html') !== FALSE || strpos($header['content_type'], 'text/xml') !== FALSE) {
            $html = new simple_html_dom();
            $html->load($content);
            return $html === false ? null : $html;
        }

    }
    return null;
}

$html = get_html('https://www.fl.ru/vakansii/');
//	if(false){
// $html = str_get_html($content);
// $html = str_get_html('<body><p>Hi</p></body>');
// echo $content;
// echo "html:";
// var_dump($html);

$last_link_file = 'last_link.txt';
if ($html !== false) {
    // $last_link = file_get_contents($last_link_file);
    $last_link = null;
    $last_link_new = null;
    $no = 12;
    foreach ($html->find('.b-post') as $node) {
        // if(--$no === 0)
        //     break;
        $link = $node->find('.b-post__title a')[0]->href;
        if ($link != $last_link) {
            if ($last_link_new == null) {
                $last_link_new = $link;
                file_put_contents($last_link_file, $last_link_new);
            }
            $html2 = get_html('https://www.fl.ru' . $link);
            $cats = $html2->find('[data-id=category-spec]');
            $cat = $cats[0]->plaintext;
            $subcat = $cats[1]->plaintext;

            if ($cat == 'Сайты' && array_search($subcat, $subcats) !== false) {
                $head = $node->find('.b-post__title')[0]->plaintext;
                $sal_scr = $node->find('.b-post__title + script')[0]->innertext;
                preg_match("/document.write\('(.+)'\)/", $sal_scr, $m);
                $sal_node = str_get_html($m[1]);
                $sal = $sal_node->plaintext;
                $body_scr = $node->find('.b-post__title + script + script')[0]->innertext;
                preg_match("/document.write\('(.+)'\)/", $body_scr, $m);
                $body_node = str_get_html($m[1]);
                $body = $body_node->find('.b-post__txt')[0]->innertext;
                $time_scr = $node->find('.b-post__foot > script')[0]->innertext;
                preg_match("/document.write\('(.+)'\)/", $time_scr, $m);
                $time_node = str_get_html($m[1]);
                $time = $time_node->find('.b-post__txt ~ span + span + span')[0]->plaintext;
                echo "<br><br>";
                echo "<h2>$head</h2>";
                echo "<h5>$link</h5>";
                echo "<h3>$sal</h3>";
                echo "<h4>$body</h4>";
                echo "<h4>$time</h4>";
                echo "<h4>$cat / $subcat</h4>";
                echo "<br><br>";
            }
        } else {
            break;
        }
    }
}
;
?>