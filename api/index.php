<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/GenericModel.php';

$response = ['status' => 'error', 'message' => 'Invalid request', 'data' => null];

$table = $_GET['table'] ?? '';
$action = $_GET['action'] ?? '';

if (empty($table)) {
    $response['message'] = 'Table name is required.';
    echo json_encode($response);
    exit;
}

try {
    $model = new GenericModel($table);
} catch (Exception $e) {
    $response['message'] = 'Failed to initialize model: ' . $e->getMessage();
    echo json_encode($response);
    exit;
}

switch ($action) {
    case 'schema':
        $rawSchema = $model->getSchema();
        if (empty($rawSchema)) {
            $response['message'] = "Table '{$table}' not found or is empty.";
            break;
        }

        $finalSchema = $rawSchema; // Começa com o schema padrão

        $schemaFilePath = __DIR__ . '/../src/schemas.php';
        if (file_exists($schemaFilePath)) {
            $customSchemas = include $schemaFilePath;
            if (isset($customSchemas[$table])) {
                $tableSchemaRules = $customSchemas[$table];
                $filteredSchema = [];

                // Mapeia o schema bruto por nome de campo para acesso fácil
                $rawSchemaMap = [];
                foreach ($rawSchema as $col) {
                    $rawSchemaMap[$col['Field']] = $col;
                }

                // Itera sobre as REGRAS do schema.php para filtrar e ordenar
                foreach ($tableSchemaRules as $fieldName => $rules) {
                    if (isset($rawSchemaMap[$fieldName])) {
                        // Mescla o schema original com as regras customizadas
                        $filteredSchema[] = array_merge($rawSchemaMap[$fieldName], $rules);
                    }
                }
                $finalSchema = $filteredSchema;
            }
        }

        $response['status'] = 'success';
        $response['message'] = 'Schema retrieved successfully.';
        $response['data'] = $finalSchema;
        break;

    case 'read':
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $sortBy = $_GET['sortBy'] ?? '1';
        $sortOrder = $_GET['sortOrder'] ?? 'ASC';
        $offset = ($page - 1) * $limit;

        $data = $model->getAll($sortBy, $sortOrder, $limit, $offset);
        $total = $model->countAll();

        $response['status'] = 'success';
        $response['message'] = 'Data retrieved successfully.';
        $response['data'] = $data;
        $response['pagination'] = [
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'totalPages' => ceil($total / $limit)
        ];
        break;

    case 'create':
        $postData = json_decode(file_get_contents('php://input'), true);
        if (!empty($postData)) {
            $id = $model->create($postData);
            $response['status'] = 'success';
            $response['message'] = 'Record created successfully.';
            $response['data'] = ['id' => $id];
        } else {
            $response['message'] = 'No data provided to create.';
        }
        break;

    case 'update':
        $postData = json_decode(file_get_contents('php://input'), true);
        $id = $_GET['id'] ?? null;
        if ($id && !empty($postData)) {
            $model->update($id, $postData);
            $response['status'] = 'success';
            $response['message'] = 'Record updated successfully.';
        } else {
            $response['message'] = 'ID or data not provided for update.';
        }
        break;

    case 'delete':
        $id = $_GET['id'] ?? null;
        if ($id) {
            $model->delete($id);
            $response['status'] = 'success';
            $response['message'] = 'Record deleted successfully.';
        } else {
            $response['message'] = 'ID not provided for deletion.';
        }
        break;

    default:
        $response['message'] = "Invalid action: {$action}";
        break;
}

echo json_encode($response);
