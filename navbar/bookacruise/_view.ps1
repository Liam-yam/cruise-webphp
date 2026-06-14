$f='navbar/bookacruise/scripts.js'
$lines = Get-Content $f
Write-Output 'Lines 690-700:'
for ($i=689; $i -lt 700; $i++) {
  $num = ('{0,4}' -f ($i+1))
  Write-Output ($num + ': [' + $lines[$i] + ']')
}