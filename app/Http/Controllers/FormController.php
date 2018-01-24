<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB as DB;
use Redirect;
use Validator;
use Session;
use App\Form;
use Idrd\Usuarios\Repo\Acceso;
use App\Localidad;
use App\Fechas;
use App\Horas;
use App\Cupo;
use Mail;



class FormController extends BaseController

{
    var $url;

     public function index()
    {

     $localidad = Localidad::all();
      //dd($horas); exit();
      return view('welcome',["localidades"=>$localidad,]);
    }

    private function cifrar($M)

    {   

      $C="";
      $k = 18; 
      for($i=0; $i<strlen($M); $i++)$C.=chr((ord($M[$i])+$k)%255);
      return $C;
    }

    private function decifrar($C)
    {   

      $M="";
      $k = 18;
      for($i=0; $i<strlen($C); $i++)$M.=chr((ord($C[$i])-$k+255)%255);
      return $M;
    }



   public function listar_datos(){

   $acceso = Form::whereYear('created_at', '=', date('Y'))->get(); 
    
    $tabla='<table id="lista">
        <thead>
           <tr>
             <th style="text-transform: capitalize;">id</th>
             <th style="text-transform: capitalize;">Entidad/Institución</th>
             <th style="text-transform: capitalize;">Encargado que asiste a la Lúdica y Docente</th> 
             <th style="text-transform: capitalize;">Cargo</th> 
             <th style="text-transform: capitalize;">Teléfono</th>  
             <th style="text-transform: capitalize;">Correo Electrónico</th>          
            </tr>
        </thead>
        <tbody id="tabla">';
      foreach ($acceso as $key => $value) {
     

       $tabla.='<tr><td>'.$value->id.'</td>';
       $tabla.='<td>'.$value->entidad.'</td>';   
       $tabla.='<td>'.$value->encargado.'</td>';   
       $tabla.='<td>'.$value->cargo.'</td>';
       $tabla.='<td>'.$value->telefono.'</td>';  
       $tabla.='<td>'.$value->mail.'</td></tr>';
       

      }
      $tabla.='</tbody></table>';

      echo $tabla;

    }

  
    public function logear(Request $request)
    {

      $usuario = $request->input('usuario');
      $pass = $request->input('pass');
      $acceso = Acceso::where('Usuario',$usuario)->where('Contrasena', sha1($this->cifrar($pass)) )->first();

      if (empty($usuario)) { return view('error',['error' => 'Usuario o contraseña invalida!'] ); exit(); }
      if (empty($acceso)) { return view('error',['error' => 'Usuario o contraseña invalida!'] ); exit(); }
       
       session_start() ;
       $_SESSION['id_usuario'] = json_encode($acceso);

      return view('admin'); exit(); 
    }


    public function insertar(Request $request)
    {

      $post = $request->input();
      //$usuario = Form::where('cedula', $request->input('cedula'))->first(); 
      if (!empty($usuario)) { return view('error',['error' => 'Este usuario ya fue registrado!'] ); exit(); }
      $formulario = new Form([]);

        //envio de correo

       if($this->inscritos()<=1000)
       {

        $this->store($formulario, $request->input());
        Mail::send('email', ['user' => $request->input('mail')], function ($m) use ($request) 
        {

            $m->from('no-reply@idrd.gov.co', 'Registro Exitoso  al Encuentro lúdica y Docente');
            $m->to($request->input('mail'), $request->input('primer_nombre'))->subject('Registro Exitoso al Registro Exitoso  al Encuentro lúdica y Docente');

        });

      }else{

        return view('error', ['error' => 'Lo sentimos el limite de inscritos fue superado!']);
      }

        return view('error', ['error' => 'Su registro fue exitoso']);
    }


// conteo de la tabla

    private function inscritos()
    {

      $cant = Form::count('id');
      return $cant+1;
    }


    private function store($formulario, $input)

    {
        $formulario['entidad'] = $input['entidad'];
        $formulario['encargado'] = $input['encargado'];
        $formulario['cargo'] = $input['cargo'];
        $formulario['telefono'] = $input['telefono'];
        $formulario['mail'] = $input['mail'];
        $formulario->save();

        return $formulario;

        

    }

}
