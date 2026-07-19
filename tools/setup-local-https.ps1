$ErrorActionPreference = 'Stop'

$apacheRoot = 'C:\wamp64\bin\apache\apache2.4.65'
$confDir = Join-Path $apacheRoot 'conf'
$extraDir = Join-Path $confDir 'extra'
$httpdConf = Join-Path $confDir 'httpd.conf'
$sslConf = Join-Path $extraDir 'httpd-ssl.conf'
$openssl = Join-Path $apacheRoot 'bin\openssl.exe'
$certDir = Join-Path $confDir 'ssl'
$ip = '192.168.1.10'
$projectPublic = 'C:/wamp64/www/siga/public'

New-Item -ItemType Directory -Path $certDir -Force | Out-Null

$opensslConfig = Join-Path $certDir 'siga-local-openssl.cnf'
$caConfig = Join-Path $certDir 'siga-local-ca-openssl.cnf'
$caKeyPath = Join-Path $certDir 'siga-local-ca.key'
$caCertPath = Join-Path $certDir 'siga-local-ca.crt'
$keyPath = Join-Path $certDir 'siga-local.key'
$certPath = Join-Path $certDir 'siga-local.crt'
$csrPath = Join-Path $certDir 'siga-local.csr'

@"
[req]
default_bits = 4096
prompt = no
default_md = sha256
distinguished_name = dn
x509_extensions = v3_ca

[dn]
CN = SIGA Local CA
O = SIGA Local
OU = Local Network

[v3_ca]
subjectKeyIdentifier = hash
authorityKeyIdentifier = keyid:always,issuer
basicConstraints = critical, CA:true
keyUsage = critical, keyCertSign, cRLSign
"@ | Set-Content -LiteralPath $caConfig -Encoding ASCII

& $openssl req -x509 -nodes -days 3650 -newkey rsa:4096 -keyout $caKeyPath -out $caCertPath -config $caConfig

@"
[req]
default_bits = 2048
prompt = no
default_md = sha256
distinguished_name = dn
x509_extensions = v3_req

[dn]
CN = $ip
O = SIGA Local
OU = Local Network

[v3_req]
subjectAltName = @alt_names
keyUsage = critical, digitalSignature, keyEncipherment
extendedKeyUsage = serverAuth

[alt_names]
IP.1 = $ip
DNS.1 = localhost
"@ | Set-Content -LiteralPath $opensslConfig -Encoding ASCII

& $openssl req -nodes -newkey rsa:2048 -keyout $keyPath -out $csrPath -config $opensslConfig
& $openssl x509 -req -in $csrPath -CA $caCertPath -CAkey $caKeyPath -CAcreateserial -out $certPath -days 3650 -sha256 -extensions v3_req -extfile $opensslConfig

Copy-Item -LiteralPath $caCertPath -Destination 'C:\wamp64\www\siga\public\siga-local-ca.crt' -Force

Copy-Item -LiteralPath $httpdConf -Destination "$httpdConf.codex-ssl-backup" -Force
Copy-Item -LiteralPath $sslConf -Destination "$sslConf.codex-ssl-backup" -Force

$httpd = Get-Content -LiteralPath $httpdConf -Raw
$httpd = $httpd.Replace('#LoadModule socache_shmcb_module modules/mod_socache_shmcb.so', 'LoadModule socache_shmcb_module modules/mod_socache_shmcb.so')
$httpd = $httpd.Replace('#LoadModule ssl_module modules/mod_ssl.so', 'LoadModule ssl_module modules/mod_ssl.so')
$httpd = $httpd.Replace('#Include conf/extra/httpd-ssl.conf', 'Include conf/extra/httpd-ssl.conf')
Set-Content -LiteralPath $httpdConf -Value $httpd -Encoding ASCII

$ssl = Get-Content -LiteralPath $sslConf -Raw
$ssl = [regex]::Replace($ssl, 'DocumentRoot "\$\{SRVROOT\}/htdocs"', 'DocumentRoot "' + $projectPublic + '"', 1)
$ssl = [regex]::Replace($ssl, 'ServerName www\.example\.com:443', 'ServerName ' + $ip + ':443', 1)
$ssl = [regex]::Replace($ssl, 'SSLCertificateFile "\$\{SRVROOT\}/conf/server\.crt"', 'SSLCertificateFile "' + ($certPath -replace '\\', '/') + '"', 1)
$ssl = [regex]::Replace($ssl, 'SSLCertificateKeyFile "\$\{SRVROOT\}/conf/server\.key"', 'SSLCertificateKeyFile "' + ($keyPath -replace '\\', '/') + '"', 1)

if ($ssl -notmatch [regex]::Escape('<Directory "' + $projectPublic + '">')) {
    $directoryBlock = @"

<Directory "$projectPublic">
    Options +Indexes +Includes +FollowSymLinks +MultiViews
    AllowOverride All
    Require all granted
</Directory>
"@
    $ssl = $ssl + $directoryBlock
}

Set-Content -LiteralPath $sslConf -Value $ssl -Encoding ASCII

netsh advfirewall firewall add rule name="SIGA Apache HTTPS 443" dir=in action=allow protocol=TCP localport=443 remoteip=LocalSubnet profile=any | Out-Host

& (Join-Path $apacheRoot 'bin\httpd.exe') -t

Restart-Service -Name wampapache64

Write-Host ''
Write-Host 'HTTPS local configurado.'
Write-Host "Abrir desde el celular: https://$ip/siga/public"
Write-Host "Certificado generado: $certPath"
