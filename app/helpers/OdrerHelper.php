<?php

use ItNetwork\HtmlBuilder;

/**
 * Soubor formátovacích metod pro objednávku
 */
class OrderHelper
{

    /**
     * Vyrenderuje grafický widget se stavem objednávky
     * @param string $state Stav objednávky
     * @param bool $registered Zda je zákazník registrovaný
     * @return string HTML kód widgetu
     */
    public static function state($state, $registered)
    {
        // Položky
        $items = array(
            array(
                'icon' => 'fa fa-shopping-cart',
                'title' => 'Košík',
                'href' => 'objednavka',
                'visible' => true,
            ),
            array(
                'icon' => 'fa fa-user',
                'title' => 'Zákazník',
                'href' => 'osoby/register',
                'visible' => !$registered,
            ),
            array(
                'icon' => 'fa fa-credit-card',
                'title' => 'Platba',
                'href' => 'objednavka/payment',
                'visible' => true,
            ),
            array(
                'icon' => 'far fa-list-alt',
                'title' => 'Rekapitulace',
                'href' => 'objednavka/summary',
                'visible' => true,
            ),
        );
        $builder = new HtmlBuilder();

        $builder->startElement('div', array(
            'class' => 'row justify-content-between mb-3 pb-2 pt-3 border-top border-bottom text-center',
            'id' => 'order-state'
        ));
        // Renderování položek
        foreach ($items as $i => $item)
        {
            if ($item['visible'])
            {
                $builder->startElement('div', array('class' => 'col-' . floor(12 / count($items))));
                $builder->startElement('a', array(
                    'href' => $item['href'],
                    'class' => $i == $state ? 'text-primary' : 'text-secondary'
                ));
                $builder->addValueElement('span', '', array(
                    'class' => $item['icon'] . ' large-icon'
                ));
                $builder->addElement('br');
                $builder->addValue($item['title']);
                $builder->endElement();
                $builder->endElement();
            }
        }
        $builder->endElement();

        return $builder->render();
    }

}
