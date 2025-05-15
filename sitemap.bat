curl -s https://xn--9oqr43f8k1a.jp-mod.net/sitemap.php
timeout /t 5

cmd /C C:/usr/nextftp/NEXTFTP_CLI.EXE $Host15 -local="G:/repogitory_jp/nobu8_site" -quit -nosound -minimize -download=sitemap.xml -nokakunin

