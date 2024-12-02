<?php

declare(strict_types=1);

namespace App\Controller;

use App\DBAL\MultiDbConnectionWrapper;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends AbstractController
{
	private EntityManagerInterface $em;

	public function __construct(EntityManagerInterface $em)
	{
		$this->em = $em;
	}

    public function list(Request $request): JsonResponse
    {
        $connection = $this->em->getConnection();
        if (!$connection instanceof MultiDbConnectionWrapper) {
            throw new \RuntimeException('Wrong connection');
        }
        $databaseName = 'test';
        $x = $connection->selectDatabase($databaseName);
        $sm = $connection->createSchemaManager();
        $tables = $sm->listTables();
        foreach ($tables as $table) {
            $columns = $sm->listTableColumns($table->getName());
            dd($table->getName(), $columns);
        }
//        listTableColumns()

        dd($tables, $connection->getParams(), $connection->getDatabase(), $connection);
        return new Response($databaseName);
    }

	public function add(Request $request): JsonResponse
	{
		$connection = $this->em->getConnection();
		if(!$connection instanceof MultiDbConnectionWrapper) {
			throw new \RuntimeException('Wrong connection');
		}
        dd($connection);

		$data = json_decode($request->getContent(), true);
		$databaseName = $data['databaseName'];
		$productName = $data['productName'];

		$connection->selectDatabase($databaseName);

		$product = new Product($productName);
		$this->em->persist($product);
		$this->em->flush();

		return new JsonResponse();
	}
}
