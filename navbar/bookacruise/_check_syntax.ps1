$f='navbar/bookacruise/scripts.js'
$lines = Get-Content $f
for ($i=690; $i -lt 705; $i++) {
  $num = ('{0,4}' -f ($i+1))
  Write-Output ($num + ': ' + $lines[$i])
}