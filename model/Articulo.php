<?php
	require "Conexion.php";

	class articulo{


		public function __construct(){
		}

		public function Registrar($idcategoria, $idunidad_medida, $nombre, $descripcion, $marca, $imagen){
			global $conexion;
			$sql = "INSERT INTO articulo(idcategoria, idunidad_medida, nombre, descripcion, imagen, marca,estado)
						VALUES($idcategoria, $idunidad_medida, '$nombre', '$descripcion', '$imagen','$marca', 'A')";
			$query = $conexion->query($sql);
			return $query;
		}

		public function Modificar($idarticulo, $idcategoria, $idunidad_medida, $nombre, $descripcion, $marca, $imagen){
			global $conexion;
			$sql = "UPDATE articulo set idcategoria = $idcategoria, idunidad_medida = $idunidad_medida, nombre = '$nombre', descripcion = '$descripcion', imagen = '$imagen', marca = '$marca'
						WHERE idarticulo = $idarticulo";
			$query = $conexion->query($sql);
			return $query;
		}

		public function Eliminar($idarticulo){
			global $conexion;
			$sql = "UPDATE articulo set estado = 'N' WHERE idarticulo = $idarticulo";
			$query = $conexion->query($sql);
			return $query;
		}

		public function Listar(){
			global $conexion;
			$sql = "select a.*, c.nombre as categoria, um.nombre as unidadMedida , a.marca as Marca
	from articulo a inner join categoria c on a.idcategoria = c.idcategoria
	inner join unidad_medida um on a.idunidad_medida = um.idunidad_medida where a.estado = 'A' order by idarticulo desc";
			$query = $conexion->query($sql);
			return $query;
		}


		public function Reporte(){
			global $conexion;
			$sql = "select a.*, c.nombre as categoria, um.nombre as unidadMedida, a.marca as Marca
	from articulo a inner join categoria c on a.idcategoria = c.idcategoria
	inner join unidad_medida um on a.idunidad_medida = um.idunidad_medida where a.estado = 'A' order by a.nombre asc";
			$query = $conexion->query($sql);
			return $query;
		}

	}
