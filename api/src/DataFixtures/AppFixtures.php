<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Product;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $product = new Product();
        $product->setName('Product 1');
        $product->setDescription('Description of product 1');
        $product->setPrice(100);
        $product->setImages([
            'image1.jpg',
            'image2.jpg',
        ]);
        $manager->persist($product);

        $manager->flush();
    }
}
