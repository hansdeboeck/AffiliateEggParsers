<?php

namespace Keywordrush\AffiliateEgg;

/**
 * Parser class file
 *
 * @author hanspunt.be <support@hanspunt.be>
 * @link https://hanspunt.be/
 * @copyright Copyright &copy; 2019 hanspunt.be
 */
class VandenborrebeParser extends ShopParser {

    protected $charset = 'utf-8';
    protected $manufacturer = 'Vandenborre';

    public function parseCatalog($max)
    {
         $urls = array_slice($this->xpathArray(".//*[@id='Producten']//a/@href"), 0, $max);
      
        if (!$urls)
            $urls = array_slice($this->xpathArray("//*[@class='js-product-click']/@href"), 0, $max);
        

        $host = parse_url($this->getUrl(), PHP_URL_HOST);
        foreach ($urls as $i => $url)
        {
            $url = "https:".$url;
           
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'https://' . $host . $url;
        }
        return $urls;
    }

    public function parseTitle()
    {
        return trim($this->xpathScalar(".//h1"));
    }

    public function parseDescription()
    {
        return $this->xpathScalar(".//*[@itemprop='description']0");
    }

    public function parsePrice()
    {
        return $this->xpathScalar(".//*[@id='prxBlock']/*/strong");
    }

  


    public function parseImg()
    {
        $img = $this->xpathScalar(".//meta[@property='og:image']/@content");
        return $img;
    }

    public function parseImgLarge()
    {
        
    }

    public function parseExtra()
    {
        $extra = array();
        $extra['rating'] = TextHelper::ratingPrepare($this->xpathScalar(".//*[@id='rating']/div/span"));
        return $extra;
    }

    public function isInStock()
    {
        if ($this->xpathScalar(".//*[@itemprop='availability']/@content") == 'out_of_stock')
            return false;
        else
            return true;
    }

}
