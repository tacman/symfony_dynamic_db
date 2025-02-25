<?php

declare(strict_types=1);

namespace App\Controller;

use App\DBAL\MultiDbConnectionWrapper;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{

	public function __construct(private EntityManagerInterface $em)
	{
	}

    #[Route('/list/{tenant}', name: 'product_list', methods: ['GET'])]
    #[Template('app/product/list.html.twig')]
    public function list(Request $request, string $tenant): Response|array
    {
        $connection = $this->em->getConnection();
        if (!$connection instanceof MultiDbConnectionWrapper) {
            throw new \RuntimeException('Wrong connection');
        }
        $databaseName = $tenant;
        $connection->selectDatabase($databaseName);
        $sm = $connection->createSchemaManager();
        $tables = $sm->listTables();
        foreach ($tables as $table) {
            $columns = $sm->listTableColumns($table->getName());
            dd($table->getName(), $columns);
        }
//        listTableColumns()

//        dd($tables, $connection->getParams(), $connection->getDatabase(), $connection);
        return  [
            'tenant' => $tenant,
            'tables' => $tables,
            'dbName' => $databaseName,
        ];
    }

    #[Route('/add', name: 'product_add', methods: ['GET', 'POST'])]
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
