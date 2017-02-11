HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
sudo /usr/bin/setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var
sudo /usr/bin/setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var

