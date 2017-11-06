# PaymentGateway for Magento 2

## Magento 2.x Community Edition telepítése

* Letöltés

    `https://magento.com/tech-resources/download`
    
    Ingyenes regisztráció szükséges. A teszt adatokat is töltsük le, egyszerűbb lesz az életünk.

* Telepítés

    - Csinájlunk egy könyvtárat a projektünkek, pl: `magento` néven
    
    - A letöltött Magentot másoljuk be ebbe könyvrárába
    
    - A sample fájlos cuccot is
    
    - Indítsuk el a traefik-ot a megszokott módon
    
    - `docker-compose build`
    
    - `docker-compose up -d`
    
    - `docker exec -it magento_web_1 /bin/bash` <br>(itt a magento_web_1 függ a docker imagéről, amelyet a könyvtár nevőből alkot)
    
    - `cd var/www/dev/magento/`
    
    - `php bin/magento setup:install --db-host=magento-db --db-name=magento --db-user=test --db-password=test --admin-firstname=FISH --admin-lastname=BIG --admin-email=<email_cim> --admin-user=bfadmin --admin-password=Nagyhal123 --use-secure=1 --base-url-secure=https://magento.dev.big.hu/ --use-secure-admin=1 --backend-frontname=admin`<br>
    (az --admin-email=\<email_cim\> opcióba valamilyen saját email címet írjunk)
    
    - Ha hiba nélkül lefut a telepítés, az utolsó sorokban valahol lesz egy ilyen:<br>
    `[SUCCESS]: Magento Admin URI: /admin`<br>
    Tehát az admin felületet a majd /admin alatt érjük el.
    
    - Nyissuk meg szövegszerkesztőben az app/etc/env.php fájlt, és módosítsuk az alábbi sorokat:<br>
    `array (
        'save' => 'files',
      ),`
    <br>erre:<br>
    `array (
        'save' => 'memcached',
        'save_path' => 'magento-memcache:11211',
      ),`
    
    - Böngészőben nézzük meg az eredményt: <br>
    https://magento.dev.big.hu/
    - Relax, sokáig tart az első indulás (is)...
    
    - Lépjünk be az adminba is: <br>
    https://magento.dev.big.hu/admin
    
    - Hell yeah!
    
## BIG FISH PaymentGateway for Magento 2 telepítése

* Letöltés
    
    
* Telepítés

    A modult másoljuk az `app/code` könyvtárba (ha nincs code könyvtár, hozzuk létre).
    A helyes könyvtárstruktúra tehát így kezdődik: `app/code/BigFish/Pmgw/...`
    
    