<?php
if (!(defined('IN_IA'))) 
{
	exit('Access Denied');
}

class ts_EweiShopV2Page extends PluginWebPage 
{
	

	11111
	
	public function main() 
		{
			
			$year = date('Y');
		$month = intval(date('m'));
		$week = 0;
	//$openid='oENK1w7iaYJxTecRNmjX-GJ_WM2U';
			global $_W;
			$set = $this->getSet();
			$days = get_last_day($year, $month);
			$starttime = strtotime($year . '-' . $month . '-1');
			$endtime = strtotime($year . '-' . $month . '-' . $days);
			$settletimes = intval($set['settledays']) * 86400;
			if ((1 <= $week) && ($week <= 4)) 
			{
				$weekdays = array();
				$i = $starttime;
				while ($i <= $endtime) 
				{
					$ds = explode('-', date('Y-m-d', $i));
					$day = intval($ds[2]);
					$w = ceil($day / 7);
					if (4 < $w) 
					{
						$w = 4;
					}
					if ($week == $w) 
					{
						$weekdays[] = $i;
					}
					$i += 86400;
				}
				$starttime = $weekdays[0];
				$endtime = strtotime(date('Y-m-d', $weekdays[count($weekdays) - 1]) . ' 23:59:59');
			}
			else 
			{
				$endtime = strtotime($year . '-' . $month . '-' . $days . ' 23:59:59');
			}
			$bill = pdo_fetch('select * from ' . tablename('ewei_shop_abonus_bill') . ' where uniacid=:uniacid and `year`=:year and `month`=:month and `week`=:week limit 1', array(':uniacid' => $_W['uniacid'], ':year' => $year, ':month' => $month, ':week' => $week));
			if (!(empty($bill)) && empty($openid)) 
			{
				return array('ordermoney' => round($bill['ordermoney'], 2), 'ordercount' => $bill['ordercount'], 'bonusmoney1' => round($bill['bonusmoney1'], 2), 'bonusmoney_send1' => round($bill['bonusmoney_send1'], 2), 'bonusmoney2' => round($bill['bonusmoney2'], 2), 'bonusmoney_send2' => round($bill['bonusmoney_send2'], 2), 'bonusmoney3' => round($bill['bonusmoney3'], 2), 'bonusmoney_send3' => round($bill['bonusmoney_send3'], 2), 'aagentcount1' => $bill['aagentcount1'], 'aagentcount2' => $bill['aagentcount2'], 'aagentcount3' => $bill['aagentcount3'], 'starttime' => $starttime, 'endtime' => $endtime, 'billid' => $bill['id'], 'old' => true);
			}
			$ordermoney = 0;
			$bonusmoney = 0;
			$orders = pdo_fetchall('select id,openid,price,agentarea from ' . tablename('ewei_shop_order') . ' where uniacid=' . $_W['uniacid'] ." and agentarea!=''  and status=3 and isabonus=0 and finishtime + " . $settletimes . '>= ' . $starttime . ' and  finishtime + ' . $settletimes . '<=' . $endtime, array(), 'id');
			foreach($orders as &$pvo){
			$ogpvprice= pdo_fetchall('select pvprice from ' . tablename('ewei_shop_order_goods') .  '   where uniacid=' . $_W['uniacid'] . '  and orderid='.$pvo['id'] );			
				foreach($ogpvprice as $ogp){
					$pvprice+=$ogp['pvprice'];
				}
			$pvo['pvprice']=$pvprice;
			unset($pvprice);
			}
			$pcondition = '';
			if (!(empty($openid))) 
			{	
				$pcondition = ' and m.openid=\'' . $openid . '\'';
			}			
			$aagents = pdo_fetchall('select m.id,m.openid,m.aagentlevel,m.aagenttype,m.aagentprovinces,m.aagentcitys,m.aagentareas,m.aagentcomms,m.aagentckcenters, l.bonus1,l.bonus2,l.bonus3,l.bonus4,l.bonus5 from ' . tablename('ewei_shop_member') . ' m ' . '  left join ' . tablename('ewei_shop_abonus_level') . ' l on l.id = m.aagentlevel ' . '  where m.uniacid=:uniacid and  m.isaagent=1 and m.aagentstatus=1 ' . $pcondition, array(':uniacid' => $_W['uniacid']));
			$aagentcount1 = 0;
			$aagentcount2 = 0;
			$aagentcount3 = 0;
			$aagentcount4 = 0;
			$aagentcount5 = 0;
			foreach ($aagents as &$a ) 
			{
				if (empty($a['aagentlevel']) || (($a['bonus1'] == NULL) && ($a['bonus2'] == NULL) && ($a['bonus3'] == NULL)&& ($a['bonus4'] == NULL)&& ($a['bonus5'] == NULL))) 
				{
					$a['bonus1'] = floatval($set['bonus1']);
					$a['bonus2'] = floatval($set['bonus2']);
					$a['bonus3'] = floatval($set['bonus3']);
					$a['bonus4'] = floatval($set['bonus4']);
					$a['bonus5'] = floatval($set['bonus5']);
				}
				$a['aagentprovinces'] = iunserializer($a['aagentprovinces']);
				$a['aagentareas'] = iunserializer($a['aagentareas']);
				$a['aagentcitys'] = iunserializer($a['aagentcitys']);
				$a['aagentcomms'] = iunserializer($a['aagentcomms']);
				$a['aagentckcenters'] = iunserializer($a['aagentckcenters']);
				if ($a['aagenttype'] == 1) 
				{
					++$aagentcount1;
				}
				else if ($a['aagenttype'] == 2) 
				{
					++$aagentcount2;
				}
				else if ($a['aagenttype'] == 3) 
				{
					++$aagentcount3;
				}
				else if ($a['aagenttype'] == 4) 
				{
					++$aagentcount4;
				}
				else if ($a['aagenttype'] == 5) 
				{
					++$aagentcount5;
				}
				$a['bonusmoney1'] = 0;
				$a['bonusmoney2'] = 0;
				$a['bonusmoney3'] = 0;
				$a['bonusmoney4'] = 0;
				$a['bonusmoney5'] = 0;
			}
			unset($a);	
			$allarea=pdo_fetchall('select * from ' . tablename('ewei_shop_abonus_area') . ' where uniacid=' . $_W['uniacid']);			
			foreach ($orders as $o ) 
			{
				$ordermoney += $o['price'];
			}			
				foreach ($aagents as &$a ) 
				{
					$bonusmoney1 = 0;
					$bonusmoney2 = 0;
					$bonusmoney3 = 0;
					$bonusmoney4 = 0;
					$bonusmoney5 = 0;					
						if(!empty($a['aagentprovinces'])){
							foreach($a['aagentprovinces'] as $zzp){
								$zzarea[]['area']=$zzp;
							}
							foreach($zzarea as &$zza){							
								foreach($allarea as $ala){
										if($ala['areaname']==$zza['area']){
											$zza['id']=$ala['id'];	
										}
								}
							}unset($zza);
							
							foreach($zzarea as $zza){							
							$zzmoney['self']=$this->getselfmoney($zza['area'],$orders);	
							$zzmoney['child']=$this->getchildmoney($zza['id'],$orders,$allarea);
							
							$voidchild=$this->getvoidchild($zza['id'],$orders,$allarea);
						
							foreach($voidchild as $voidc){
								$voidmoney+=round(($voidc['self'] * $a['bonus'.$voidc['level']]) / 100, 2)+round(($voidc['child'] * abs($a['bonus'.$voidc['level']]-$a['bonus'.($voidc['level']+1)])) / 100, 2);
							}
							$bonusmoney1 += round(($zzmoney['self'] * $a['bonus1']) / 100, 2)+round(($zzmoney['child'] * abs($a['bonus1']-$a['bonus2'])) / 100, 2)+$voidmoney;
							
							}
							unset($zzmoney);
							unset($voidc);
							unset($voidchild);
							unset($voidmoney);
							unset($ala);
							unset($zza);
							unset($zzp);
							unset($zzarea);
							
							
							
						}
						if(!empty($a['aagentcitys'])){
							foreach($a['aagentcitys'] as $zzp){
								$zzarea[]['area']=$zzp;
							}
							foreach($zzarea as &$zza){							
								foreach($allarea as $ala){
										if($ala['areaname']==$zza['area']){
											$zza['id']=$ala['id'];
										}
								}
							}unset($zza);
							
							foreach($zzarea as $zza){
							$zzmoney['self']=$this->getselfmoney($zza['area'],$orders);	
							$zzmoney['child']=$this->getchildmoney($zza['id'],$orders,$allarea);
							$voidchild=$this->getvoidchild($zza['id'],$orders,$allarea);
							
							foreach($voidchild as $voidc){
								$voidmoney+=round(($voidc['self'] * $a['bonus'.$voidc['level']]) / 100, 2)+round(($voidc['child'] * abs($a['bonus'.$voidc['level']]-$a['bonus'.($voidc['level']+1)])) / 100, 2);
							}
							$bonusmoney2 += round(($zzmoney['self'] * $a['bonus2']) / 100, 2)+round(($zzmoney['child'] * abs($a['bonus2']-$a['bonus3'])) / 100, 2)+$voidmoney;

							}
							unset($voidc);
							unset($voidchild);
							unset($voidmoney);
							unset($ala);
							unset($zza);
							unset($zzp);
							unset($zzarea);
							unset($zzmoney);

						}
						if(!empty($a['aagentareas'])){
							foreach($a['aagentareas'] as $zzp){
								$zzarea[]['area']=$zzp;
							}
							foreach($zzarea as &$zza){							
								foreach($allarea as $ala){
										if($ala['areaname']==$zza['area']){
											$zza['id']=$ala['id'];
										}
								}
							}unset($zza);
							foreach($zzarea as $zza){												
							$zzmoney['self']=$this->getselfmoney($zza['area'],$orders);	
							$zzmoney['child']=$this->getchildmoney($zza['id'],$orders,$allarea);
							$voidchild=$this->getvoidchild($zza['id'],$orders,$allarea);
							foreach($voidchild as $voidc){
								$voidmoney+=round(($voidc['self'] * $a['bonus'.$voidc['level']]) / 100, 2)+round(($voidc['child'] * abs($a['bonus'.$voidc['level']]-$a['bonus'.($voidc['level']+1)])) / 100, 2);
							}
							$bonusmoney3 += round(($zzmoney['self'] * $a['bonus3']) / 100, 2)+round(($zzmoney['child'] * abs($a['bonus3']-$a['bonus4'])) / 100, 2)+$voidmoney;
							}
							unset($voidc);
							unset($voidchild);
							unset($voidmoney);
							unset($ala);
							unset($zza);
							unset($zzp);
							unset($zzarea);
							unset($zzmoney);

						}
						if(!empty($a['aagentcomms'])){
							foreach($a['aagentcomms'] as $zzp){
								$zzarea[]['area']=$zzp;
							}
							foreach($zzarea as &$zza){							
								foreach($allarea as $ala){
										if($ala['areaname']==$zza['area']){
											$zza['id']=$ala['id'];
										}
								}
							}unset($zza);
							
							foreach($zzarea as $zza){							
	
							$zzmoney['self']=$this->getselfmoney($zza['area'],$orders);	
							$zzmoney['child']=$this->getchildmoney($zza['id'],$orders,$allarea);
							$voidchild=$this->getvoidchild($zza['id'],$orders,$allarea);
							foreach($voidchild as $voidc){
								$voidmoney+=round(($voidc['self'] * $a['bonus'.$voidc['level']]) / 100, 2)+round(($voidc['child'] * abs($a['bonus'.$voidc['level']]-$a['bonus'.($voidc['level']+1)])) / 100, 2);
							}
							$bonusmoney4 += round(($zzmoney['self'] * $a['bonus4']) / 100, 2)+round(($zzmoney['child'] * abs($a['bonus4']-$a['bonus5'])) / 100, 2)+$voidmoney;
							}
							unset($voidc);
							unset($voidchild);
							unset($voidmoney);
							unset($ala);
							unset($zza);
							unset($zzp);
							unset($zzarea);
							unset($zzmoney);

						}
						if(!empty($a['aagentckcenters'])){
							foreach($a['aagentckcenters'] as $zzp){
								$zzarea[]['area']=$zzp;
							}
							foreach($zzarea as &$zza){							
								foreach($allarea as $ala){
										if($ala['areaname']==$zza['area']){
											$zza['id']=$ala['id'];
										}
								}
							}unset($zza);
							foreach($zzarea as $zza){								
							$zzmoney['self']=$this->getselfmoney($zza['area'],$orders);	
							$bonusmoney5 += round(($zzmoney['self'] * $a['bonus5']) / 100, 2);
							}
							unset($voidc);
							unset($voidchild);
							unset($voidmoney);
							unset($ala);
							unset($zza);
							unset($zzp);
							unset($zzarea);
							unset($zzmoney);

						}
					$a['bonusmoney1'] += $bonusmoney1;					
					$a['bonusmoney2'] += $bonusmoney2;
					$a['bonusmoney3'] += $bonusmoney3;
					$a['bonusmoney4'] += $bonusmoney4;
					$a['bonusmoney5'] += $bonusmoney5;
					unset($a);
				}	
			
			$bonusmoney = 0;
			$bonusmoney1 = 0;
			$bonusmoney2 = 0;
			$bonusmoney3 = 0;
			$bonusmoney4 = 0;
			$bonusmoney5 = 0;
			foreach ($aagents as &$a ) 
			{
				$bonusmoney_send = 0;
				$bonusmoney_send1 = 0;
				$bonusmoney_send2 = 0;
				$bonusmoney_send3 = 0;
				$bonusmoney_send4 = 0;
				$bonusmoney_send5 = 0;
				$a['charge'] = 0;
				$a['chargemoney'] = 0;
				
				if ((floatval($set['paycharge']) <= 0) || ((floatval($set['paybegin']) <= $a['bonusmoney1'] + $a['bonusmoney2'] + $a['bonusmoney3']+ $a['bonusmoney4']+ $a['bonusmoney5']) && ($a['bonusmoney1'] + $a['bonusmoney2'] + $a['bonusmoney3']+ $a['bonusmoney4']+ $a['bonusmoney5'] <= floatval($set['payend'])))) 
				{
					
					$bonusmoney_send1 += round($a['bonusmoney1'], 2);
					$bonusmoney_send2 += round($a['bonusmoney2'], 2);
					$bonusmoney_send3 += round($a['bonusmoney3'], 2);
					$bonusmoney_send4 += round($a['bonusmoney4'], 2);
					$bonusmoney_send5 += round($a['bonusmoney5'], 2);
				}
				else 
				{	
					$bonusmoney_send1 += round($a['bonusmoney1'] - (($a['bonusmoney1'] * floatval($set['paycharge'])) / 100), 2);
					$bonusmoney_send2 += round($a['bonusmoney2'] - (($a['bonusmoney2'] * floatval($set['paycharge'])) / 100), 2);
					$bonusmoney_send3 += round($a['bonusmoney3'] - (($a['bonusmoney3'] * floatval($set['paycharge'])) / 100), 2);
					$bonusmoney_send4 += round($a['bonusmoney4'] - (($a['bonusmoney4'] * floatval($set['paycharge'])) / 100), 2);
					$bonusmoney_send5 += round($a['bonusmoney5'] - (($a['bonusmoney5'] * floatval($set['paycharge'])) / 100), 2);
					$a['charge'] = floatval($set['paycharge']);
					$a['chargemoney1'] = round(($a['bonusmoney1'] * floatval($set['paycharge'])) / 100, 2);
					$a['chargemoney2'] = round(($a['bonusmoney2'] * floatval($set['paycharge'])) / 100, 2);
					$a['chargemoney3'] = round(($a['bonusmoney3'] * floatval($set['paycharge'])) / 100, 2);
					$a['chargemoney4'] = round(($a['bonusmoney4'] * floatval($set['paycharge'])) / 100, 2);
					$a['chargemoney5'] = round(($a['bonusmoney5'] * floatval($set['paycharge'])) / 100, 2);
				}
				$a['bonusmoney_send1'] = $bonusmoney_send1;
				$a['bonusmoney_send2'] = $bonusmoney_send2;
				$a['bonusmoney_send3'] = $bonusmoney_send3;
				$a['bonusmoney_send4'] = $bonusmoney_send4;
				$a['bonusmoney_send5'] = $bonusmoney_send5;
				$bonusmoney1 += $bonusmoney_send1;
				$bonusmoney2 += $bonusmoney_send2;
				$bonusmoney3 += $bonusmoney_send3;
				$bonusmoney4 += $bonusmoney_send4;
				$bonusmoney5 += $bonusmoney_send5;
			}
			unset($p);		
			$res=array('orders' => $orders, 'aagents' => $aagents, 'ordermoney' => round($ordermoney, 2), 'ordercount' => count($orders), 'bonusmoney1' => round($bonusmoney1, 2), 'bonusmoney2' => round($bonusmoney2, 2), 'bonusmoney3' => round($bonusmoney3, 2),'bonusmoney4' => round($bonusmoney4, 2),'bonusmoney5' => round($bonusmoney5, 2), 'aagentcount1' => $aagentcount1, 'aagentcount2' => $aagentcount2, 'aagentcount3' => $aagentcount3,'aagentcount4' => $aagentcount4,'aagentcount5' => $aagentcount5, 'starttime' => $starttime, 'endtime' => $endtime, 'old' => false);		
		print_r($res);
		}
		 public function getchildren($categorys,$parentid=0,$level=1){  
						$subs=array();  
			foreach($categorys as $item){  
				if($item['parentid']==$parentid){  
					$item['funlevel']=$level;  
					$subs[]=$item;  
					$subs=array_merge($subs,$this->getchildren($categorys,$item['id'],$level+1));  
					  
				}  
					  
			}  
			return $subs;  
		}
		 public function getselfmoney($areaname,$orders){  
					$moneyres=0;
			 foreach ($orders as $o ) {
				 if ($o['agentarea']==$areaname) 
						{							
							$moneyres+=$o['pvprice'];
						}
			 }
			return $moneyres;  
		}
		 public function getchildmoney($id,$orders,$allarea){ 
			$moneyres=0;				 
			$childareas=$this->getchildren($allarea, $id, 0);
					if(empty($childareas)){
						$moneyres=0;
						return $moneyres; 
					}				
				foreach($childareas as $chda){
					$moneyres+=$this->getselfmoney($chda['areaname'],$orders);	
				}
			 
			return $moneyres;  
		}		
		 public function getvoidchild($id,$orders,$allarea){  		
				$subs=array();  
			foreach($allarea as $ala){  
				if($ala['parentid']==$id&&$ala['isp']==0){ 
					$ala[self]=$this->getselfmoney($ala['areaname'],$orders);
					$ala['child']=$this->getchildmoney($ala['id'],$orders,$allarea);					
					$subs[]=$ala;  
					$subs=array_merge($subs,$this->getvoidchild($ala['id'],$orders,$allarea));  
					  
				}  
					  
			}  
			return $subs;  
		}
		
}
?>