<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class UserController extends Controller
{
  /**
   * El repositorio de usuarios.
   *
   * @var UserRepository
  */
  protected UserRepository $userRepository;

  /**
   * Inyecta el repositorio en el controlador.
   *
   * @param UserRepository $userRepository
  */
  public function __construct(UserRepository $userRepository)
  {
    $this->middleware(['check_permission:access_users'])->only(
      [
        'index',
        'show',
        'store',
        'edit',
        'update',
        'destroy',
        'deleteSelected',
        'datatable',
        'create'
      ]
    );

    $this->userRepository = $userRepository;
  }

  /**
   * Muestra una lista de usuarios.
   *
   * @return View
  */
  public function index(): View
  {
    $users = $this->userRepository->getAllUsers();
    return view('users.index', compact('users'));
  }

  /**
   * Muestra el formulario para crear un nuevo usuario.
   *
   * @return View
  */
  public function create(): View
  {
    return view('users.create');
  }

  /**
   * Muestra los detalles de un usuario específico.
   *
   * @param int $id
   * @return JsonResponse
  */
  public function show(int $id): JsonResponse
  {
    $user = $this->userRepository->getUserById($id);
    return response()->json($user);
  }

  /**
   * Almacena un nuevo usuario en la base de datos.
   *
   * @param StoreUserRequest $request
   * @return JsonResponse
  */
  public function store(StoreUserRequest $request): JsonResponse
  {
    $result = $this->userRepository->createUser($request->validated());
    return response()->json($result);
  }

  /**
   * Muestra el formulario para editar un usuario existente.
   *
   * @param int $id
   * @return View
  */
  public function edit(int $id): View
  {
    $user = $this->userRepository->getUserById($id);
    return view('users.edit', compact('user'));
  }

  /**
   * Actualiza un usuario existente en la base de datos.
   *
   * @param UpdateUserRequest $request
   * @param int $id
   * @return JsonResponse
  */
  public function update(UpdateUserRequest $request, int $id): JsonResponse
  {
      $data = $request->validated();

      // Si no se proporciona una nueva contraseña, elimínala de los datos de la solicitud
      if (empty($data['password'])) {
          unset($data['password']);
      }

      $result = $this->userRepository->updateUser($id, $data);
      return response()->json($result);
  }



  /**
   * Elimina un usuario específico de la base de datos.
   *
   * @param int $id
   * @return JsonResponse
  */
  public function destroy(int $id): JsonResponse
  {
    $result = $this->userRepository->deleteUser($id);
    return response()->json($result);
  }

  /**
   * Elimina los usuarios seleccionados de la base de datos.
   *
   * @param Request $request
   * @return JsonResponse
  */
  public function deleteSelected(Request $request): JsonResponse
  {
    $result = $this->userRepository->deleteSelectedUsers($request->ids);
    return response()->json($result);
  }

  /**
   * Obtiene los datos para mostrar en la tabla de usuarios.
   *
   * @return JsonResponse
   */
  public function datatable(): JsonResponse
  {
      $users = $this->userRepository->datatable();
      return response()->json(['data' => $users]);
  }

}
