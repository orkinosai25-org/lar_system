<?php
/**
 * @package    Provab Application
 * @subpackage Travel Portal
 * @author     Balu A<balu.provab@gmail.com>
 * @version    V2
 */
interface Report_Model
{
	 public function booking(array $condition, bool $count, int $offset, int $limit): array|string;
}