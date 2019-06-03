<?php

namespace Keywordrush\AffiliateEgg;

/**
 * CoolbluebeParser class file
 *
 * @author hanspunt.be <support@hanspunt.be>
 * @link https://hanspunt.be/
 * @copyright Copyright &copy; 2019 hanspunt.be
 */
class CoolbluebeParser extends LdShopParser{

    protected $charset = 'utf-8';
    protected $currency = 'EUR';

    public function parseCatalog($max)
    {
        $urls = array_slice($this->xpathArray(".//*[@id='direct-naar-resultaten' or @id='facet_productlist']//h2/a/@href"), 0, $max);
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//*[@class='product__display']//a/@href"), 0, $max);

        if (!$urls)
            $urls = array_slice($this->xpathArray(".//a[contains(@class, 'product__title')]/@href"), 0, $max);
        
        // top lists
        if (!$urls)
            $urls = array_slice($this->xpathArray(".//*[@class='productlist_item_top_product']//h4/a/@href"), 0, $max);

        $host = parse_url($this->getUrl(), PHP_URL_HOST);
        foreach ($urls as $i => $url)
        {
            if (!preg_match('/^https?:\/\//', $url))
                $urls[$i] = 'https://' . $host . $url;
        }
        return $urls;
    }

    public function parseOldPrice()
    {
        $price = $this->xpathScalar(".//*[@class='grid-section-xs--gap-s']//*[@class='sales-price--former']");
        $price = str_replace(',', '', $price);
        return $price;
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $extra['features'] = array();
        $names = $this->xpathArray(".//*[@class='product-specs--help-title']");
        $values = $this->xpathArray(".//*[@class='product-specs--item-spec']");
        $feature = array();
        for ($i = 0; $i < count($names); $i++)
        {
            if (empty($values[$i]))
                continue;
            $feature['name'] = sanitize_text_field($names[$i]);
            $feature['value'] = sanitize_text_field($values[$i]);
            $extra['features'][] = $feature;
        }

        return $extra;
    }
    
    public function isInStock()
    {
        if (isset($this->ld_json['offers'][0]['availability']))
            $availability = $this->ld_json['offers'][0]['availability'];
        elseif (isset($this->ld_json['offers']['availability']))
            $availability = $this->ld_json['offers']['availability'];
        else
            $availability = '';

        if ($availability && in_array($availability, array('OutOfStock', 'SoldOut', 'http://schema.org/OutOfStock', 'https://schema.org/OutOfStock', 'http://schema.org/SoldOut')))
            return false;
        else
            return true;
    }

}
