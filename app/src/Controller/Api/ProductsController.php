<?php

namespace App\Controller\Api;

use App\Entity\Product;
use App\Form\ProductType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api/products", name="api_products_")
 */
class ProductsController extends AbstractController
{
    /**
     * @Route("/", methods={"GET"}, name="index")
     * 
     * example: curl --location  'http://localhost:8050/api/products' --header 'Content-Type: application/json' 
     */
    public function index(): JsonResponse
    {
        $products = $this->getDoctrine()->getRepository(Product::class)->findAll();

        return $this->json($products);
    }

    /**
     * @Route("/load-records", methods={"POST"}, name="load_records")
     * 
     * example: curl --location --request POST 'http://localhost:8050/api/products/load-records' --header 'Content-Type: application/json' --data '[{"sku":"001", "product_name":"Producto 1","description":"kjahsdkahsd kasd"},{"sku":"002", "product_name":"Producto 2","description":"kjahsdkahsd kasd"}]'
     */
    public function create(Request $request): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();

        // Obtener el contenido JSON de la solicitud
        $products = json_decode($request->getContent(), true);

        $response = [];

        foreach ($products as $productRequest) {
            $entityManager = $this->getDoctrine()->resetManager();

            $currentResponse = ['sku' => $productRequest['sku']];
            $product = new Product();
            $product->setSku($productRequest['sku']);
            $product->setProductName($productRequest['product_name']);
            $product->setDescription($productRequest['description']);

            try{
              // Persistir la entidad en la base de datos
                $entityManager->persist($product);
                $entityManager->flush();

                $currentResponse['status'] = 'ok';
                $currentResponse['error'] = false;

            } catch (\Exception $e) {
                // Capturar cualquier excepción y responder con un mensaje de error
                // se puede mejorar este mensaje de error....
                $currentResponse['status'] = 'failed';
                $currentResponse['error'] = $e->getMessage();
            }

            $response[] = $currentResponse;
        }

        return $this->json($response, 201);
    }


    /**
     * @Route("/update-existing-records", methods={"PUT"}, name="update_existing_records")
     * 
     * example: curl --location --request PUT 'http://localhost:8050/api/products/update-existing-records' --header 'Content-Type: application/json' --data '[{"sku":"001", "product_name":"Producto 1","description":"kjahsdkahsd kasd"},{"sku":"003", "product_name":"Producto 2","description":"kjahsdkahsd kasd"}]'
     */
    public function update(Request $request): JsonResponse
    {
        $entityManager = $this->getDoctrine()->getManager();
        $productRepository = $entityManager->getRepository(Product::class);

        // Obtener el contenido JSON de la solicitud
        $products = json_decode($request->getContent(), true);

        $response = [];

        foreach ($products as $productRequest) {

            $entityManager = $this->getDoctrine()->resetManager();

            $currentResponse = ['sku' => $productRequest['sku']];

            $product = $productRepository->findOneBy(['sku' => $productRequest['sku']]);

            if($product){
                $product->setProductName($productRequest['product_name']);
                $product->setDescription($productRequest['description']);

                try{
                  // Persistir la entidad en la base de datos
                    $entityManager->persist($product);
                    $entityManager->flush();

                    $currentResponse['status'] = 'ok';
                    $currentResponse['error'] = false;

                } catch (\Exception $e) {
                    // Capturar cualquier excepción y responder con un mensaje de error
                    $currentResponse['status'] = 'failed';
                    $currentResponse['error'] = $e->getMessage();
                }
            } else {
                $currentResponse['status'] = 'failed';
                $currentResponse['error'] = 'Product is not exist!';

            }
          
            $response[] = $currentResponse;
        }

        return $this->json($response, 201);
    }

}
