<?php

namespace App\Controllers\GraphQLSample;

use Atom\Controllers\Controller as BaseController;
use Atom\Http\Request;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use GraphQL\GraphQL;
use GraphQL\Utils\BuildSchema;
use App\GraphQL\Resolvers\AdditionResolver;
use App\GraphQL\Resolvers\EchoerResolver;
use App\Models\User;
use Atom\File\Log;
use App\GraphQL\Resolvers\ListUsersResolver;
use App\GraphQL\Resolvers\ShowUserResolver;
use App\GraphQL\Resolvers\CreateUserResolver;

class GraphQLUserController extends BaseController
{
    /**
     * User
     *
     * @var User
     */
    protected $user;

    /**
     * Schema path
     *
     * @var string
     */
    protected $schemaPath;

    /**
     * Construct
     * @param User $user User
     */
    public function __construct(User $user)
    {
        parent::__construct();
        $this->user = $user;
        $this->schemaPath = DOC_ROOT.'/../app/GraphQL/Schema/';
    }

    /**
     * List users
     *
     * @return json
     */
    public function list()
    {
        Log::info(__METHOD__);
        try {
            $userType = new ObjectType([
                'name' => 'User',
                'fields' => [
                    'id' => ['type' => Type::int()],
                    'fullname' => ['type' => Type::string()],
                    'email' => ['type' => Type::string()],
                    'photo' => ['type' => Type::string()],
                    'gender' => ['type' => Type::string()],
                ],
            ]);
            $queryType = new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'users' => [
                        'type' => Type::listOf($userType),
                        'resolve' => function ($root, $args) {
                            $root;
                            $args;
                            return $this->getUsers();
                        }
                    ],
                ],
            ]);

            $schema = new Schema([
                'query' => $queryType,
            ]);
            $request = $this->request->all();
            $query =  $request['query'];
            $variableValues = isset($data['variables']) ? $data['variables'] : null;

            $result = GraphQL::executeQuery($schema, $query, null, null, $variableValues);
            $output = $result->toArray();
        } catch (\Exception $e) {
            $output = [
                'error' => [
                    'message' => $e->getMessage()
                ]
            ];
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($output);
    }

    /**
     * Get Users
     *
     * @return array
     */
    public function getUsers()
    {
        $this->user->enableQueryLog();
        $users = $this->user->get();
        Log::info($this->user->getQueryLog());
        return $users;
    }

    /**
     * List Users via shorthand
     *
     * @return json
     */
    public function shorthandList()
    {
        try {
            $schema = BuildSchema::build(file_get_contents($this->schemaPath. 'user_schema.graphql'));
            $rootValue = $this->rootValueList();
            $request = $this->request->all();
            $query =  $request['query'];
            $variableValues = isset($data['variables']) ? $data['variables'] : null;

            $result = GraphQL::executeQuery($schema, $query, $rootValue, null, $variableValues);
        } catch (\Exception $e) {
             $result = [
                'error' => [
                    'message' => $e->getMessage()
                ]
             ];
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($result);
    }

    /**
     * Root value for List uers
     *
     * @return array
     */
    public function rootValueList()
    {
        return [
            'users' => function ($rootValue, $args, $context) {
                $usersResolver = new ListUsersResolver();
                return $usersResolver->resolve($rootValue, $args, $context);
            },
        ];
    }

    /**
     * Show User via shorthand
     *
     * @return json
     */
    public function shorthandShow()
    {
        Log::info(__METHOD__);
        try {
            $schema = BuildSchema::build(file_get_contents($this->schemaPath. 'user_schema.graphql'));
            $rootValue = $this->rootValueShow();
            $request = $this->request->all();
            $query =  $request['query'];
            $variableValues = isset($data['variables']) ? $data['variables'] : null;

            $result = GraphQL::executeQuery($schema, $query, $rootValue, null, $variableValues);
        } catch (\Exception $e) {
             $result = [
                'error' => [
                    'message' => $e->getMessage()
                ]
             ];
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($result);
    }

    /**
     * Root value for Show User
     *
     * @return array
     */
    public function rootValueShow()
    {
        return [
            'user' => function ($rootValue, $args, $context) {
                $userResolver = new ShowUserResolver();
                return $userResolver->resolve($rootValue, $args, $context);
            },
        ];
    }

    /**
     * Create a new User
     *
     * @return json
     */
    public function create()
    {
        Log::info(__METHOD__);
        try {
            $schema = BuildSchema::build(file_get_contents($this->schemaPath. 'user_schema.graphql'));
            $rootValue = $this->rootValueCreate();
            $request = $this->request->all();
            $query =  $request['query'];
            $variableValues = isset($data['variables']) ? $data['variables'] : null;

            $result = GraphQL::executeQuery($schema, $query, $rootValue, null, $variableValues);
        } catch (\Exception $e) {
             $result = [
                'error' => [
                    'message' => $e->getMessage()
                ]
             ];
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($result);
    }

    /**
     * Root value for Create User
     *
     * @return array
     */
    public function rootValueCreate()
    {
        return [
            'create' => function ($rootValue, $args, $context) {
                $userResolver = new CreateUserResolver();
                return $userResolver->resolve($rootValue, $args, $context);
            },
        ];
    }
}
