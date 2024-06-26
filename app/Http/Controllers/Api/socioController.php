<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use App\Models\Socio;
use Illuminate\Support\Facades\Hash;
use App\Models\Persona;
use Illuminate\Support\Facades\Validator;

class socioController extends Controller
{
    public function index(){
         $socios = Socio::all();
         if($socios->isEmpty()){
            $data = [
                'message' => 'No se encontraron usuarios',
                'status' => 400,
            ];
         }else{
            $data = [
                'message' => 'Usuarios encontrados',
                'status' => 200,
                'usuarios' => $socios
            ];
         }
         return response()->json($data,200);
    }

    public function store (Request $request)
    {
        $validacion = Validator::make($request->all(),[
            'nombre' => 'required', 'string', 'regex:/^(?!\s)(?!.*\s$)[a-zA-Z\s]*[a-zA-Z]+[a-zA-Z\s]*$/','max:85',
            'primer_apellido' => 'required', 'string' , 'regex:/^[a-zA-Z]+$/' ,'max:85',
            'segundo_apellido' => 'nullable','string', 'regex:/^[a-zA-Z]+$/','max:85',
            'ci' => 'required', 'string' , 'regex: /^[a-zA-Z0-9]+$/', 'max:40'
        ]);

        if ($validacion -> fails()){
            $data = [
                'message' => 'Error, al validar datos',
                'status' => 400,
                'errores' => $validacion -> errors()
            ];
            return response()->json($data,200);
        }
        try {

            $esta_registrado = Socio::usuarioExistente($request->nombre,$request->primer_apellido,$request->segundo_apellido);

            if(!$esta_registrado){
                $socio = Socio::create([
                    'nombre_socio' => $request->nombre,
                    'primer_apellido_socio' => $request->primer_apellido,
                    'segundo_apellido_socio' => $request->segundo_apellido,
                    'ci_socio' => $request->ci
                ]);
            }

            $esta_registrada_cuenta = Usuario::cuentaExistente($request->username);

            if(!$esta_registrada_cuenta){

                $id_usuario = Socio::buscar_id_usuario($request->nombre,$request->primer_apellido,$request->segundo_apellido);
                $contrasenia_encriptada = Hash::make($request->contrasenia);

                $cuenta = Usuario::create([
                    'username' => $request->username,
                    'contrasenia' => $contrasenia_encriptada,
                    'email' => $request->email,
                    'socio_id' => $id_usuario->id
                ]);

                $data = [
                    'message' => 'Cuenta creada exitosamente. Solo cuenta.',
                    'status' => 200,
                    'usuario' => $cuenta
                ];
                return response()->json($data, 200);
            }else{
                $data = [
                    'message' => 'Usuario y cuenta ya registrados.',
                    'status' => 400
                ];
                return response()->json($data, 200);
            }

        } catch (\Exception $e) {
            $data = [
                'message' => 'Error al crear el usuario: ' . $e->getMessage(),
                'status' => 500,
            ];
            return response()->json($data, 500);
        }
    }

    public function show($id){
        $usuario = Socio::find($id);

        if(!$usuario){
            $data = [
                'message' => 'Usuario no encontrado',
                'status' => 404
            ];
            return response()->json($data,404);
        }

        $data = [
            'usuario' => $usuario,
            'status' => 200
        ];

        return response()->json($data,200);
    }

    public function destroy($id){
        $usuario = Socio::find($id);

        if(!$usuario){
            $data = [
                'message' => 'Usuario no encontrado',
                'status' => 404
            ];
            return response()->json($data,404);
        }

        $usuario -> delete();

        $data = [
            'message' => 'Usuario eliminado',
            'status' => 200
        ];

        return response()->json($data,200);
    }

    public function update (Request $request,$id){

        $usuario = Socio::find($id);

        if(!$usuario){
            $data = [
                'message' => 'Usuario no encontrado',
                'status' => 404
            ];
            return response()->json($data,404);
        }

        $validacion = Validator::make($request->all(),[
            'nombre' => 'required', 'string', 'regex:/^(?!\s)(?!.*\s$)[a-zA-Z\s]*[a-zA-Z]+[a-zA-Z\s]*$/','max:45',
            'primerApellido' => 'required', 'string' , 'regex:/^[a-zA-Z]+$/' ,'max:45',
            'segundoApellido' => 'nullable','string', 'regex:/^[a-zA-Z]+$/','max:45'
        ]);

        if ($validacion -> fails()){
            $data = [
                'message' => 'Error en la validacion de datos',
                'status' => 400,
                'errores' => $validacion -> errors()
            ];
            return response()->json($data,400);
        }

        $usuario->nombre = $request->nombre;
        $usuario->primerApellido = $request->primerApellido;
        $usuario->segundoApellido = $request->segundoApellido;

        $usuario->save();

        $data = [
            'message' => 'Datos actualizados',
            'usuario' => $usuario,
            'status' => 200
        ];

        return response()->json($data,200);

    }

    public function update_parcial(Request $request, $id){

        $usuario = Socio::find($id);

        if(!$usuario){
            $data = [
                'message' => 'Usuario no encontrado',
                'status' => 404
            ];
            return response()->json($data,404);
        }

        $validacion = Validator::make($request->all(),[
            'nombre' =>'string', 'regex:/^(?!\s)(?!.*\s$)[a-zA-Z\s]*[a-zA-Z]+[a-zA-Z\s]*$/','max:45',
            'primerApellido' =>'string' , 'regex:/^[a-zA-Z]+$/' ,'max:45',
            'segundoApellido' => 'nullable','string', 'regex:/^[a-zA-Z]+$/','max:45'
        ]);

        if ($validacion -> fails()){
            $data = [
                'message' => 'Error en la validacion de datos',
                'status' => 400,
                'errores' => $validacion -> errors()
            ];
            return response()->json($data,400);
        }

        if($request->has('nombre')){
            $usuario->nombre = $request->nombre;
        }
        if($request->has('primerApellido')){
            $usuario->primerApellido = $request->primerApellido;
        }
        if($request->has('segundoApellido')){
            $usuario->segundoApellido = $request->segundoApellido;
        }

        $usuario->save();

        $data = [
            'message' => 'Estudiante actualizado',
            'usuario' => $usuario,
            'status' => 200
        ];

        return response()->json($data,200);

    }

}
