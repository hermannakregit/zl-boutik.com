<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class CategoryFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i=0; $i < 5 ; $i++) { 
            
            $category = new Category();

            $name = $faker->name;

            $category->setName($name);
            $category->setSlug($name);
            $category->setActive(true);

            $manager->persist($category);

        }

        $manager->flush();
    }
}
