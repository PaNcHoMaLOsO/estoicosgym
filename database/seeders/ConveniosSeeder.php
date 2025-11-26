<?php

namespace Database\Seeders;

use App\Models\Convenio;
use Illuminate\Database\Seeder;

class ConveniosSeeder extends Seeder
{
    public function run(): void
    {
        // Instituciones Educativas
        Convenio::create([
            'nombre' => 'INACAP',
            'tipo' => 'institucion_educativa',
            'descripcion' => 'Instituto de Capacitación - Estudiantes y egresados',
            'contacto_nombre' => 'Departamento de Bienestar',
            'contacto_email' => 'bienestar@inacap.cl',
            'contacto_telefono' => '+56227891000',
            'activo' => true,
        ]);

        Convenio::create([
            'nombre' => 'DUOC UC',
            'tipo' => 'institucion_educativa',
            'descripcion' => 'Instituto Profesional - Estudiantes y trabajadores',
            'contacto_nombre' => 'Departamento de Vida Universitaria',
            'contacto_email' => 'vida.universitaria@duocuc.cl',
            'contacto_telefono' => '+56227197000',
            'activo' => true,
        ]);

        Convenio::create([
            'nombre' => 'Universidad Andrés Bello',
            'tipo' => 'institucion_educativa',
            'descripcion' => 'UNAB - Estudiantes y personal administrativo',
            'contacto_nombre' => 'Dirección de Bienestar',
            'contacto_email' => 'bienestar@unab.cl',
            'contacto_telefono' => '+56227703000',
            'activo' => true,
        ]);

        // Empresas
        Convenio::create([
            'nombre' => 'Cruz Verde',
            'tipo' => 'empresa',
            'descripcion' => 'Cadena de farmacias - Personal y ejecutivos',
            'contacto_nombre' => 'Recursos Humanos',
            'contacto_email' => 'rrhh@cruzverde.cl',
            'contacto_telefono' => '+56226481000',
            'activo' => true,
        ]);

        Convenio::create([
            'nombre' => 'Falabella',
            'tipo' => 'empresa',
            'descripcion' => 'Retail - Personal tiendas',
            'contacto_nombre' => 'Relaciones Laborales',
            'contacto_email' => 'rrll@falabella.cl',
            'contacto_telefono' => '+56223835000',
            'activo' => true,
        ]);

        Convenio::create([
            'nombre' => 'Banco Santander',
            'tipo' => 'empresa',
            'descripcion' => 'Entidad financiera - Personal y clientes gold',
            'contacto_nombre' => 'Beneficios Corporativos',
            'contacto_email' => 'beneficios@santander.cl',
            'contacto_telefono' => '+56226381234',
            'activo' => true,
        ]);

        Convenio::create([
            'nombre' => 'Clínica Montefiore',
            'tipo' => 'empresa',
            'descripcion' => 'Centro médico - Personal administrativo y asistencial',
            'contacto_nombre' => 'Departamento de RR.HH.',
            'contacto_email' => 'rrhh@montefiore.cl',
            'contacto_telefono' => '+56227244000',
            'activo' => true,
        ]);

        // Organizaciones
        Convenio::create([
            'nombre' => 'Colegio de Ingenieros',
            'tipo' => 'organizacion',
            'descripcion' => 'Asociación profesional - Miembros activos',
            'contacto_nombre' => 'Secretaría General',
            'contacto_email' => 'beneficios@ingenieros.cl',
            'contacto_telefono' => '+56226395700',
            'activo' => true,
        ]);

        Convenio::create([
            'nombre' => 'Cámara de Comercio Santiago',
            'tipo' => 'organizacion',
            'descripcion' => 'Asociación empresarial - Socios y afiliados',
            'contacto_nombre' => 'Departamento de Afiliados',
            'contacto_email' => 'afiliados@ccsantiago.cl',
            'contacto_telefono' => '+56227973000',
            'activo' => true,
        ]);

        Convenio::create([
            'nombre' => 'Club de Empresarios',
            'tipo' => 'organizacion',
            'descripcion' => 'Red de negocios - Miembros premium',
            'contacto_nombre' => 'Coordinador de Beneficios',
            'contacto_email' => 'beneficios@clubempresarios.cl',
            'contacto_telefono' => '+56227445566',
            'activo' => true,
        ]);

        // Sin descuento (referencia)
        Convenio::create([
            'nombre' => 'Miembro Regular',
            'tipo' => 'otro',
            'descripcion' => 'Membresía sin convenio especial',
            'activo' => true,
        ]);
    }
}
