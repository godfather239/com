<?php 
 interface LoadSourceData{
 	public function read_original_data();
 	public function read_delta_data($ids);
 	public function field_transform($data);
 	public function write_xml($data);
	 public function get_dir_path();
 }