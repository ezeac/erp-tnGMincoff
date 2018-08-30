curl https://www.tiendanube.com/apps/authorize/token --data 'client_id=799&client_secret=btKMXvakhd6vPTnKlVtdc6pqVEkhAK1TlYZrLRWC6LTeMXjz&grant_type=authorization_code&code=ca5f6ea457b7ec09b660f8279087dbdfd4412463'


{
    "access_token": "f65d278761a83bdf2e5887200f51f6373030ee39",
    "token_type": "bearer",
    "scope": "read_content,write_content,read_products,write_products,read_coupons,write_coupons,read_customers,write_customers,read_orders,write_orders,write_scripts",
    "user_id": 808724
}


curl -H "Authentication: bearer f65d278761a83bdf2e5887200f51f6373030ee39 " -H "User-Agent: App Integración ERP (ezequielcrosa@diezweb.com.ar)" https://api.tiendanube.com/v1/808724/categories

/products/5123

$salida = shell_exec('curl -H "Authentication: bearer f65d278761a83bdf2e5887200f51f6373030ee39 " -H "User-Agent: App Integración ERP (ezequielcrosa@diezweb.com.ar)" https://api.tiendanube.com/v1/808724/categories'); echo $salida;

//update product

curl -H 'Authentication: bearer f65d278761a83bdf2e5887200f51f6373030ee39 ' \
  -H 'Content-Type: application/json' \
  -H 'User-Agent: App Integración ERP (ezequielcrosa@diezweb.com.ar)' \
  -d '{ "categories": [4567], "id": 1234, "published": false}' \
  https://api.tiendanube.com/v1/808724/products/5123