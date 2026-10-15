# -*- coding: utf-8 -*-
"""Genera la Guía de Estudio de comandos de Linux (Ubuntu) en PDF."""

from pathlib import Path

from fpdf import FPDF

OUT = Path(__file__).resolve().parent / "Guia-Estudio-Comandos-Linux.pdf"
WIN_FONTS = Path(r"C:\Windows\Fonts")
LOCAL_FONTS = Path(__file__).resolve().parent / "fonts"


def prepare_fonts():
    """Copia fuentes del sistema y quita MERG para que fpdf2 no coma los espacios."""
    from fontTools.ttLib import TTFont

    LOCAL_FONTS.mkdir(exist_ok=True)
    mapping = {
        "Body.ttf": "arial.ttf",
        "Body-Bold.ttf": "arialbd.ttf",
        "Body-Italic.ttf": "ariali.ttf",
        "Code.ttf": "consola.ttf",
        "Code-Bold.ttf": "consolab.ttf",
    }
    for dest_name, src_name in mapping.items():
        dest = LOCAL_FONTS / dest_name
        if dest.exists():
            continue
        font = TTFont(str(WIN_FONTS / src_name))
        if "MERG" in font:
            del font["MERG"]
        font.save(str(dest))
    return LOCAL_FONTS

ORANGE = (233, 84, 32)
AUBERGINE = (44, 0, 30)
INK = (32, 32, 36)
MUTED = (90, 90, 96)
RULE = (230, 226, 222)
CODE_BG = (32, 34, 40)
CODE_FG = (232, 232, 232)
WHITE = (255, 255, 255)
CREAM = (252, 250, 247)
CARD = (247, 243, 239)


def C(name, desc, example, note=""):
    return {"name": name, "desc": desc, "example": example, "note": note}


CHAPTERS = [
    (
        "Cómo usar esta guía",
        "Esta guía reúne los comandos que se usan de verdad en Ubuntu y en Linux en general: "
        "GNU coreutils, administración del sistema, red, paquetes apt y systemd. "
        "No es cada binario de /usr/bin (hay cientos de herramientas opcionales), "
        "sino el conjunto completo con el que se estudia, trabaja y administra un sistema.\n\n"
        "Cómo estudiar: abre una terminal, crea una carpeta de práctica y ejecuta cada ejemplo. "
        "Lee la salida. Cambia una opción. Vuelve a ejecutar. Eso graba el comando mejor que memorizar listas.",
        [
            C(
                "Carpeta de práctica",
                "Crea un sitio seguro para experimentar. Nunca practiques rm, chmod o sudo en carpetas del sistema.",
                "$ mkdir -p ~/practica && cd ~/practica\n$ pwd\n/home/usuario/practica",
                "Todos los ejemplos de esta guía asumen que estás dentro de ~/practica, salvo que se indique otra ruta.",
            ),
            C(
                "man / --help",
                "Cada comando tiene su propio manual. Si olvidas una opción, no improvises: consulta.",
                "$ man ls\n$ ls --help | head",
                "En man: flechas o espacio para bajar, /texto para buscar, q para salir.",
            ),
            C(
                "Anatomía de un comando",
                "La forma general es: comando [opciones] [argumentos]. El espacio separa cada pieza.",
                "$ ls -la /etc\n#  ls   = comando\n#  -la  = opciones (largo + ocultos)\n#  /etc = argumento (la carpeta)",
                "Un guion corto (-l) es una opción corta. Dos guiones (--all) es la forma larga.",
            ),
        ],
    ),
    (
        "Navegación",
        "Si no sabes moverte, el resto de comandos no sirven. Empieza aquí hasta hacerlo sin pensar.",
        [
            C(
                "pwd",
                "Print Working Directory. Muestra la ruta absoluta de la carpeta donde estás.",
                "$ pwd\n/home/usuario/practica",
                "Si te pierdes, pwd es el primer comando que debes teclear.",
            ),
            C(
                "cd",
                "Change Directory. Entra a otra carpeta. Sin argumentos vuelve a tu home.",
                "$ cd /etc\n$ pwd\n/etc\n$ cd ~\n$ pwd\n/home/usuario\n$ cd -\n/etc",
                "Atajos: ~ home, . carpeta actual, .. padre, - carpeta anterior, / raíz del sistema.",
            ),
            C(
                "ls",
                "Lista el contenido de una carpeta. Es el comando que más vas a usar.",
                "$ ls\nbackup  frutas.txt  notas.txt\n$ ls -la\ndrwxr-xr-x  3 usuario usuario 4096 sep 22 10:00 .\n-rw-r--r--  1 usuario usuario   12 sep 22 10:01 notas.txt",
                "-l detalle, -a ocultos, -h tamaños legibles, -t por fecha, -R recursivo. Combínalos: ls -lah.",
            ),
            C(
                "tree",
                "Dibuja el árbol de carpetas. En Ubuntu se instala con apt.",
                "$ sudo apt install tree\n$ tree ~/practica\n/home/usuario/practica\n├── backup\n│   └── notas-copia.txt\n└── frutas.txt",
                "tree -L 2 limita la profundidad. tree -d muestra solo carpetas.",
            ),
            C(
                "pushd / popd / dirs",
                "Apilan carpetas para ir y volver sin reescribir rutas largas.",
                "$ pwd\n/home/usuario/practica\n$ pushd /etc\n/etc ~/practica\n$ popd\n~/practica",
                "dirs -v lista la pila numerada. Útil cuando saltas entre 2 o 3 proyectos.",
            ),
        ],
    ),
    (
        "Inspeccionar archivos y rutas",
        "Antes de editar o borrar, identifica qué es cada cosa: archivo, carpeta, enlace o binario.",
        [
            C(
                "file",
                "Adivina el tipo real del archivo (no solo la extensión).",
                "$ file notas.txt\nnotas.txt: ASCII text\n$ file /bin/ls\n/bin/ls: ELF 64-bit LSB pie executable",
                "Una foto renombrada a .txt sigue siendo imagen. file lo detecta.",
            ),
            C(
                "stat",
                "Muestra metadatos: tamaño, dueño, permisos, fechas de acceso y modificación.",
                "$ stat notas.txt\n  File: notas.txt\n  Size: 12         Blocks: 8     IO Block: 4096  regular file\nAccess: (0644/-rw-r--r--)  Uid: (1000/usuario)",
                "Más completo que ls -l. Sirve para depurar permisos y fechas.",
            ),
            C(
                "basename",
                "Devuelve solo el nombre final de una ruta.",
                "$ basename /home/usuario/practica/notas.txt\nnotas.txt",
                "Muy usado dentro de scripts para extraer el nombre de un archivo.",
            ),
            C(
                "dirname",
                "Devuelve la carpeta de una ruta, sin el archivo.",
                "$ dirname /home/usuario/practica/notas.txt\n/home/usuario/practica",
                "Complemento de basename.",
            ),
            C(
                "realpath",
                "Resuelve . .. y enlaces simbólicos y entrega la ruta absoluta real.",
                "$ realpath ./notas.txt\n/home/usuario/practica/notas.txt",
                "Útil cuando un script recibe rutas relativas y necesita la canónica.",
            ),
            C(
                "readlink",
                "Muestra a dónde apunta un enlace simbólico.",
                "$ ln -s notas.txt atajo.txt\n$ readlink atajo.txt\nnotas.txt\n$ readlink -f atajo.txt\n/home/usuario/practica/notas.txt",
                "-f resuelve la cadena completa hasta el archivo final.",
            ),
            C(
                "namei",
                "Descompone una ruta y muestra de qué tipo es cada eslabón.",
                "$ namei -l /home/usuario/practica/notas.txt\nf: /home/usuario/practica/notas.txt\ndr-xr-xr-x root    root    /\ndrwxr-xr-x usuario usuario practica",
                "Sirve para cazar un permiso roto a mitad de una ruta larga.",
            ),
            C(
                "lsattr / chattr",
                "Atributos extra de ext4, independientes de rwx. +i hace un archivo inmutable.",
                "$ sudo chattr +i importante.txt\n$ lsattr importante.txt\n----i--------- importante.txt\n$ sudo chattr -i importante.txt",
                "Un archivo con +i no se borra ni se edita, ni siquiera con root, hasta quitar el atributo.",
            ),
        ],
    ),
    (
        "Crear y borrar",
        "Crea estructura con mkdir y touch. Borra solo cuando sepas exactamente qué estás borrando.",
        [
            C(
                "mkdir",
                "Crea una o más carpetas. -p crea padres que falten y no falla si ya existen.",
                "$ mkdir curso\n$ mkdir -p curso/linux/dia1\n$ ls curso/linux\ndia1",
                "Sin -p, mkdir curso/linux falla si curso no existe.",
            ),
            C(
                "rmdir",
                "Borra una carpeta solo si está vacía. Es más seguro que rm -r.",
                "$ mkdir vacia\n$ rmdir vacia",
                "Si tiene archivos, usa rm -r después de revisar el contenido.",
            ),
            C(
                "touch",
                "Crea un archivo vacío o actualiza su fecha de modificación si ya existe.",
                "$ touch diario.txt\n$ ls -l diario.txt\n-rw-r--r-- 1 usuario usuario 0 sep 22 10:05 diario.txt",
                "También se usa para refrescar la fecha: touch archivo_ya_existente.",
            ),
            C(
                "rm",
                "Borra archivos. -r borra carpetas. -i pregunta. -f no pregunta.",
                "$ rm diario.txt\n$ rm -r curso",
                "NUNCA ejecutes rm -rf / ni rm -rf * fuera de ~/practica. No hay papelera.",
            ),
            C(
                "shred",
                "Sobrescribe un archivo antes de borrarlo, para que cueste recuperarlo.",
                "$ echo secreto > clave.txt\n$ shred -u -z -n 3 clave.txt",
                "-u borra al terminar, -z pone ceros al final, -n 3 hace 3 pasadas. No es magia en SSD.",
            ),
            C(
                "unlink",
                "Elimina un nombre de archivo (un enlace). Equivale a rm de un solo archivo.",
                "$ touch tmp.dat\n$ unlink tmp.dat",
                "Solo admite un archivo. Para varios, usa rm.",
            ),
            C(
                "mktemp",
                "Crea un archivo o carpeta temporal con nombre aleatorio, seguro para scripts.",
                "$ tmp=$(mktemp)\n$ echo $tmp\n/tmp/tmp.xK91ab\n$ rm $tmp",
                "mktemp -d crea una carpeta temporal.",
            ),
            C(
                "truncate",
                "Cambia el tamaño de un archivo. Puede vaciarlo o agrandarlo.",
                "$ echo hola > demo.txt\n$ truncate -s 0 demo.txt\n$ wc -c demo.txt\n0 demo.txt",
                "truncate -s 1M disco.img crea un archivo de 1 mebibyte.",
            ),
        ],
    ),
    (
        "Copiar, mover y enlaces",
        "cp duplica, mv cambia de sitio o de nombre, ln crea otro nombre que apunta al mismo contenido.",
        [
            C(
                "cp",
                "Copia archivos. -r copia carpetas. -v muestra lo que hace. -i pregunta antes de pisar.",
                "$ cp notas.txt backup-notas.txt\n$ cp -r curso/ curso-copia/",
                "cp origen destino. Si destino es carpeta, copia dentro. cp -a preserva permisos y fechas.",
            ),
            C(
                "mv",
                "Mueve o renombra. En el mismo disco es instantáneo: solo cambia la ruta.",
                "$ mv backup-notas.txt notas-antigua.txt\n$ mv notas-antigua.txt backup/",
                "No hay comando rename básico en todos los sistemas. mv archivo.txt nuevo.txt renombra.",
            ),
            C(
                "ln",
                "Crea un enlace duro: dos nombres para los mismos datos en disco.",
                "$ echo dato > original.txt\n$ ln original.txt duro.txt\n$ echo cambio >> original.txt\n$ cat duro.txt\ndato\ncambio",
                "Si borras original.txt, duro.txt sigue teniendo el contenido. No funciona entre discos distintos.",
            ),
            C(
                "ln -s",
                "Crea un enlace simbólico (atajo). Apunta a una ruta, no a los datos.",
                "$ ln -s /var/log logs\n$ ls -l logs\nlrwxrwxrwx 1 usuario usuario 8 sep 22 10:10 logs -> /var/log",
                "Si mueves el destino, el enlace se rompe. Es el tipo de enlace que más se usa.",
            ),
            C(
                "install",
                "Copia un archivo y de paso le pone dueño y permisos. Típico al instalar un script.",
                "$ install -m 755 script.sh /usr/local/bin/miscript",
                "Más control que cp cuando dejas un programa en el PATH.",
            ),
            C(
                "rsync",
                "Copia incremental, local o remota. Solo transfiere lo que cambió.",
                "$ rsync -av curso/ curso-backup/\nsending incremental file list\ndia1/\ndia1/apuntes.txt",
                "-a archivo (permisos, fechas), -v verbose, -n simula, --delete iguala borrando el exceso.",
            ),
        ],
    ),
    (
        "Ver el contenido de archivos",
        "Para archivos pequeños usa cat. Para logs largos usa less. Para las últimas líneas, tail.",
        [
            C(
                "cat",
                "Concatenate. Muestra uno o varios archivos enteros en pantalla.",
                "$ echo hola > a.txt\n$ echo mundo > b.txt\n$ cat a.txt b.txt\nhola\nmundo",
                "También une archivos: cat a.txt b.txt > juntos.txt. No lo uses con binarios.",
            ),
            C(
                "tac",
                "Igual que cat, pero de abajo hacia arriba (cat al revés).",
                "$ printf 'uno\\ndos\\ntres\\n' > nums.txt\n$ tac nums.txt\ntres\ndos\nuno",
                "Útil para ver el final de un log corto sin tail.",
            ),
            C(
                "less",
                "Visor por páginas. No carga el archivo entero en memoria.",
                "$ less /var/log/syslog",
                "Espacio avanza, b retrocede, /busca, n siguiente, q sale. Prefiérelo a more.",
            ),
            C(
                "more",
                "Visor antiguo, solo avanza. En la práctica usa less.",
                "$ more /etc/hosts",
                "Lo encontrarás en scripts viejos y en documentación clásica.",
            ),
            C(
                "head",
                "Muestra las primeras líneas. Por defecto 10.",
                "$ seq 20 > veinte.txt\n$ head -n 3 veinte.txt\n1\n2\n3",
                "head -c 20 archivo muestra los primeros 20 bytes.",
            ),
            C(
                "tail",
                "Muestra las últimas líneas. -f sigue el archivo en vivo (logs).",
                "$ tail -n 2 veinte.txt\n19\n20\n$ tail -f /var/log/syslog",
                "Ctrl+C sale de tail -f. Imprescindible para depurar servicios.",
            ),
            C(
                "nl",
                "Numera las líneas de un archivo.",
                "$ nl frutas.txt\n     1  manzana\n     2  pera\n     3  platano",
                "Útil para citar una línea concreta al hablar de un error.",
            ),
            C(
                "wc",
                "Word count. Cuenta líneas, palabras y bytes.",
                "$ echo 'hola linux ubuntu' > frase.txt\n$ wc frase.txt\n 1  3 18 frase.txt",
                "wc -l solo líneas, -w palabras, -c bytes, -m caracteres.",
            ),
            C(
                "od / hexdump",
                "Vuelca un archivo en octal o hexadecimal. Sirve para ver bytes ocultos.",
                "$ printf 'ABC\\n' | od -c\n0000000   A   B   C  \\n",
                "hexdump -C es más legible para inspección binaria.",
            ),
            C(
                "strings",
                "Extrae cadenas de texto legibles de un binario.",
                "$ strings /bin/ls | head\n...",
                "Útil para inspeccionar un programa sin abrirlo en un editor hexadecimal.",
            ),
            C(
                "tee",
                "Copia la entrada a un archivo y también la deja pasar por la tubería.",
                "$ echo 'backup ok' | tee registro.txt\nbackup ok\n$ cat registro.txt\nbackup ok",
                "tee -a añade al final. Ver y guardar al mismo tiempo.",
            ),
        ],
    ),
    (
        "Editores en terminal",
        "Tienes que poder cambiar un archivo sin entorno gráfico. Empieza por nano; luego aprende a salir de vim.",
        [
            C(
                "nano",
                "Editor sencillo. Los atajos aparecen abajo. Ideal para empezar.",
                "$ nano notas.txt\n# Escribes texto\n# Ctrl+O guarda, Enter confirma el nombre\n# Ctrl+X sale",
                "Ctrl+K corta la línea, Ctrl+U pega, Ctrl+W busca.",
            ),
            C(
                "vim",
                "Editor potente, modal. Por defecto estás en modo normal, no puedes escribir hasta pulsar i.",
                "$ vim notas.txt\n# i  = insertar texto\n# Esc = volver a modo normal\n# :w  = guardar\n# :q  = salir\n# :wq = guardar y salir\n# :q! = salir sin guardar",
                "Si vim 'te atrapa', pulsa Esc y escribe :q! Enter. Practica solo esos seis atajos al principio.",
            ),
            C(
                "echo y redirección",
                "Para cambios de una línea no siempre hace falta un editor.",
                "$ echo 'primera linea' > notas.txt\n$ echo 'segunda linea' >> notas.txt\n$ cat notas.txt\nprimera linea\nsegunda linea",
                "> crea o pisa el archivo. >> añade al final. Confundirlos borra trabajo.",
            ),
        ],
    ),
    (
        "Permisos y propiedad",
        "Linux decide quién puede leer, escribir o ejecutar. Si un script 'no funciona', casi siempre son permisos.",
        [
            C(
                "ls -l (leer permisos)",
                "La primera columna es el mapa de permisos: tipo + dueño + grupo + otros.",
                "$ ls -l script.sh\n-rwxr-xr-x 1 usuario usuario 120 sep 22 11:00 script.sh",
                "- archivo, d carpeta, l enlace. r=4 w=2 x=1. rwxr-xr-x = 755. rw-r--r-- = 644.",
            ),
            C(
                "chmod",
                "Cambia permisos. Puedes usar números (755) o letras (u+x).",
                "$ touch script.sh\n$ chmod 755 script.sh\n$ ls -l script.sh\n-rwxr-xr-x 1 usuario usuario 0 sep 22 11:01 script.sh\n$ chmod u+x,g-w archivo.txt",
                "u usuario, g grupo, o others, a all. + añade, - quita, = asigna. Evita 777.",
            ),
            C(
                "chown",
                "Cambia el dueño y, si quieres, el grupo. Casi siempre requiere sudo.",
                "$ sudo chown usuario:usuario archivo.txt\n$ sudo chown -R usuario:usuario ~/practica",
                "La forma dueño:grupo es la más habitual. -R aplica a toda la carpeta.",
            ),
            C(
                "chgrp",
                "Cambia solo el grupo del archivo.",
                "$ sudo chgrp sudo notas.txt\n$ ls -l notas.txt\n-rw-r--r-- 1 usuario sudo 12 sep 22 11:02 notas.txt",
                "Más preciso que chown cuando el dueño ya es correcto.",
            ),
            C(
                "umask",
                "Máscara de permisos por defecto para archivos y carpetas nuevos.",
                "$ umask\n0022\n$ umask -S\nu=rwx,g=rx,o=rx",
                "0022 → archivos 644 y carpetas 755. 0077 → solo tú puedes leer lo nuevo.",
            ),
            C(
                "getfacl / setfacl",
                "ACL: permisos extra para usuarios concretos, más finos que rwx clásico.",
                "$ sudo setfacl -m u:ana:r notas.txt\n$ getfacl notas.txt\nuser::rw-\nuser:ana:r--\ngroup::r--",
                "Aparece una + al final de ls -l cuando hay ACL.",
            ),
        ],
    ),
    (
        "Usuarios y grupos",
        "Linux es multi-usuario. Tu cuenta es un UID; sudo te presta poderes de root un comando cada vez.",
        [
            C(
                "whoami",
                "Imprime el usuario con el que se ejecuta la sesión actual.",
                "$ whoami\nusuario",
                "Después de sudo -i cambiará a root. Compruébalo siempre.",
            ),
            C(
                "id",
                "Muestra UID, GID y todos los grupos del usuario.",
                "$ id\nuid=1000(usuario) gid=1000(usuario) grupos=1000(usuario),27(sudo),100(users)",
                "Si un permiso falla, id te dice a qué grupos perteneces de verdad.",
            ),
            C(
                "who / w / users",
                "Quién está conectado ahora. w añade qué está haciendo y desde cuándo.",
                "$ who\nusuario  tty2  2026-09-22 09:01\n$ w",
                "Útil en servidores compartidos.",
            ),
            C(
                "last / lastlog",
                "Historial de inicios de sesión. lastlog es la última vez de cada cuenta.",
                "$ last -n 5\nusuario  tty2  still logged in\n$ lastlog | head",
                "Sirve para auditar accesos extraños.",
            ),
            C(
                "sudo",
                "Ejecuta un comando como administrador. Te pedirá tu contraseña, no la de root.",
                "$ sudo apt update\n[sudo] password for usuario:",
                "sudo -i abre una shell de root. sudo !! repite el comando anterior con sudo.",
            ),
            C(
                "su",
                "Cambia de usuario. su - usuario carga su entorno completo.",
                "$ su - otro\nPassword:",
                "En Ubuntu moderno se prefiere sudo. root suele tener el login deshabilitado.",
            ),
            C(
                "passwd",
                "Cambia la contraseña de tu usuario. Con sudo, la de otro.",
                "$ passwd\nCurrent password:\nNew password:",
                "sudo passwd usuario resetea la de esa cuenta.",
            ),
            C(
                "adduser / useradd",
                "Crea un usuario. En Ubuntu usa adduser: te hace preguntas y crea el home.",
                "$ sudo adduser maria\n$ sudo useradd -m -s /bin/bash pedro",
                "useradd es más bajo nivel y no crea home salvo que pongas -m.",
            ),
            C(
                "usermod / userdel",
                "Modifica o elimina una cuenta. -aG añade a un grupo sin quitar los demás.",
                "$ sudo usermod -aG sudo maria\n$ sudo userdel -r pedro",
                "Sin -a, -G reemplaza todos los grupos extra. Es un error clásico.",
            ),
            C(
                "groupadd / groupdel / groups",
                "Administra grupos. groups lista los grupos de un usuario.",
                "$ sudo groupadd proyecto\n$ groups usuario\nusuario sudo",
                "Los grupos controlan acceso compartido a carpetas de equipo.",
            ),
            C(
                "visudo",
                "Edita /etc/sudoers de forma segura: valida la sintaxis antes de guardar.",
                "$ sudo visudo",
                "Nunca edites sudoers con nano a pelo: un error te deja sin sudo.",
            ),
        ],
    ),
    (
        "Buscar archivos y texto",
        "Hay dos búsquedas distintas: find localiza archivos; grep localiza texto dentro de archivos.",
        [
            C(
                "find",
                "Recorre el árbol de carpetas y filtra por nombre, tipo, tamaño o fecha.",
                "$ find ~/practica -name '*.txt'\n/home/usuario/practica/notas.txt\n$ find . -type d\n$ find . -size +1M",
                "-name distingue mayúsculas; -iname no. -type f archivo, d carpeta. -mtime -1 últimos 1 día.",
            ),
            C(
                "locate / updatedb",
                "Busca por una base de datos, mucho más rápido que find. Puede estar desactualizado.",
                "$ sudo updatedb\n$ locate notas.txt\n/home/usuario/practica/notas.txt",
                "En Ubuntu el paquete es plocate o mlocate. No ve archivos creados hace 1 minuto hasta updatedb.",
            ),
            C(
                "which",
                "Muestra qué ejecutable se usaría si tecleas ese comando.",
                "$ which python3\n/usr/bin/python3",
                "Si hay varios python, which te dice cuál gana según el PATH.",
            ),
            C(
                "whereis",
                "Busca binario, código fuente y página man de un comando.",
                "$ whereis ls\nls: /usr/bin/ls /usr/share/man/man1/ls.1.gz",
                "Más amplio que which.",
            ),
            C(
                "type",
                "Dice si un nombre es comando, alias, función o builtin del shell.",
                "$ type ls\nls is aliased to `ls --color=auto'\n$ type cd\ncd is a shell builtin",
                "Resuelve la duda '¿por qué este ls se comporta distinto?'.",
            ),
            C(
                "grep",
                "Busca un patrón de texto. Es el comando estrella junto con las tuberías.",
                "$ echo -e 'error disco\\nok\\nERROR red' > log.txt\n$ grep -i error log.txt\nerror disco\nERROR red",
                "-i ignora mayúsculas, -n número de línea, -R recursivo, -v invierte, -c cuenta.",
            ),
            C(
                "grep con tubería",
                "El uso más habitual: filtrar la salida de otro comando.",
                "$ ps aux | grep ssh\n$ dmesg | grep -i error",
                "grep 'palabra' archivo busca en archivo. comando | grep busca en la salida.",
            ),
        ],
    ),
    (
        "Procesar texto",
        "Linux trata casi todo como texto. Encadena estos filtros con | y resuelves el 80% de las tareas.",
        [
            C(
                "echo / printf",
                "Imprime texto. printf da formato preciso (saltos, columnas, números).",
                "$ echo hola\nhola\n$ printf '%s %d\\n' ubuntu 24\nubuntu 24",
                "echo -e interpreta \\n. En scripts serios se prefiere printf.",
            ),
            C(
                "sort",
                "Ordena líneas. -n orden numérico, -r inverso, -k columna, -u únicos.",
                "$ printf '10\\n2\\n1\\n' | sort -n\n1\n2\n10",
                "Sin -n, 10 iría antes que 2 porque ordena como texto.",
            ),
            C(
                "uniq",
                "Quita líneas repetidas consecutivas. Casi siempre va detrás de sort.",
                "$ printf 'a\\na\\nb\\na\\n' | sort | uniq\na\nb",
                "uniq -c cuenta repeticiones. uniq solo no detecta duplicados si no están juntos.",
            ),
            C(
                "cut",
                "Corta columnas de cada línea, por carácter o por delimitador.",
                "$ echo 'ana:x:1001:1001' | cut -d: -f1,3\nana:1001",
                "-d delimitador, -f campos. Ideal para /etc/passwd y CSV simples.",
            ),
            C(
                "paste",
                "Pega archivos en columnas, línea a línea.",
                "$ echo -e 'a\\nb' > x.txt\n$ echo -e '1\\n2' > y.txt\n$ paste x.txt y.txt\na       1\nb       2",
                "El inverso informal de cut.",
            ),
            C(
                "tr",
                "Traduce o borra caracteres. No entiende regex: trabaja letra a letra.",
                "$ echo 'Hola Linux' | tr 'A-Z' 'a-z'\nhola linux\n$ echo 'a  b   c' | tr -s ' '\na b c",
                "-d borra. tr -d '\\n' quita saltos de línea. -s comprime repeticiones.",
            ),
            C(
                "sed",
                "Editor de flujo. Cambia texto al vuelo, línea por línea, sin abrir el archivo.",
                "$ echo 'me gusta windows' | sed 's/windows/linux/'\nme gusta linux\n$ sed -i 's/hola/adios/g' notas.txt",
                "s/viejo/nuevo/ sustituye. g = todas en la línea. -i edita el archivo (haz copia antes).",
            ),
            C(
                "awk",
                "Procesa columnas. El comodín cuando cut se queda corto.",
                "$ echo 'ana 8 9' | awk '{print $1, ($2+$3)/2}'\nana 8.5\n$ awk -F: '{print $1}' /etc/passwd | head",
                "$1 primera columna, NF número de campos, NR número de línea. -F cambia el separador.",
            ),
            C(
                "xargs",
                "Convierte líneas de entrada en argumentos de otro comando.",
                "$ find . -name '*.tmp' | xargs rm -f\n$ echo 'a.txt b.txt' | xargs touch",
                "xargs -n 1 ejecuta una vez por ítem. xargs -I {} rm {} usa un marcador.",
            ),
            C(
                "split / csplit",
                "Parte un archivo grande en trozos. split por tamaño o líneas; csplit por patrón.",
                "$ seq 100 > cien.txt\n$ split -l 40 cien.txt parte_\n$ ls parte_*\nparte_aa  parte_ab  parte_ac",
                "cat parte_* > cien.txt vuelve a unirlos.",
            ),
            C(
                "column",
                "Alinea texto en columnas bonitas.",
                "$ printf 'nombre edad\\nana 21\\nlucas 19\\n' | column -t\nnombre  edad\nana     21\nlucas   19",
                "column -t -s: formatea archivos separados por dos puntos.",
            ),
            C(
                "rev",
                "Invierte cada línea carácter a carácter.",
                "$ echo linux | rev\nxunil",
                "Truco de scripts y ejercicios de terminal, poco uso diario.",
            ),
            C(
                "seq",
                "Genera secuencias de números. Perfecto para pruebas.",
                "$ seq 3\n1\n2\n3\n$ seq 2 2 8\n2\n4\n6\n8",
                "seq INICIO PASO FIN. Combínalo con xargs o bucles for.",
            ),
            C(
                "shuf",
                "Baraja líneas al azar.",
                "$ seq 5 | shuf\n3\n1\n5\n2\n4",
                "shuf -n 1 elige un elemento aleatorio.",
            ),
            C(
                "yes",
                "Imprime una cadena sin parar. Se usa para responder automáticamente sí.",
                "$ yes | head -n 3\ny\ny\ny",
                "yes | comando alimenta confirmaciones. Ctrl+C para detenerlo si se va de madre.",
            ),
            C(
                "expr / test / [ ]",
                "Aritmética y condiciones en shell. [[ ]] en bash es la forma moderna.",
                "$ expr 3 + 4\n7\n$ test -f notas.txt && echo existe\nexiste\n$ [ -d backup ] && echo carpeta",
                "-f archivo, -d carpeta, -z cadena vacía, -eq igual numérico. En bash: [[ -f notas.txt ]].",
            ),
        ],
    ),
    (
        "Comparar archivos e integridad",
        "Para ver diferencias usa diff. Para comprobar que un archivo no se corrompió, usa sumas hash.",
        [
            C(
                "diff",
                "Compara dos archivos línea a línea y muestra lo que cambió.",
                "$ echo hola > a.txt\n$ echo hala > b.txt\n$ diff a.txt b.txt\n1c1\n< hola\n---\n> hala",
                "diff -u formato unificado (el de git). diff -r compara carpetas.",
            ),
            C(
                "cmp",
                "Compara byte a byte. Solo te dice si son distintos y en qué posición.",
                "$ cmp a.txt b.txt\na.txt b.txt differ: byte 2, line 1",
                "Más bajo nivel que diff. Útil con binarios.",
            ),
            C(
                "comm",
                "Compara dos archivos YA ORDENADOS y reparte líneas en 3 columnas.",
                "$ comm <(echo -e 'a\\nb') <(echo -e 'b\\nc')\na\n                b\n        c",
                "Col1 solo en el primero, col2 solo en el segundo, col3 en ambos.",
            ),
            C(
                "md5sum / sha256sum",
                "Calcula una huella del archivo. Si cambia un bit, cambia el hash.",
                "$ echo hola > a.txt\n$ sha256sum a.txt\n5891b5b5...  a.txt",
                "Compara el hash publicado por un sitio con el de tu descarga. sha256 es el habitual.",
            ),
            C(
                "cksum / sum / b2sum",
                "Otras sumas de comprobación. cksum es clásico de UNIX; b2sum usa BLAKE2.",
                "$ cksum a.txt\n3015617425 5 a.txt",
                "Para seguridad moderna, quédate con sha256sum o sha512sum.",
            ),
        ],
    ),
    (
        "Comprimir y empaquetar",
        "tar empaqueta. gzip, bzip2 y xz comprimen. zip es el formato más compatible con Windows.",
        [
            C(
                "tar",
                "Tape archive. Junta muchos archivos en uno. Suele ir con gzip: .tar.gz",
                "$ tar -czvf curso.tar.gz curso/\n$ tar -tzvf curso.tar.gz\n$ tar -xzvf curso.tar.gz",
                "-c crear, -x extraer, -t listar, -z gzip, -j bzip2, -J xz, -v verbose, -f archivo.",
            ),
            C(
                "gzip / gunzip / zcat",
                "Comprime un archivo suelto a .gz y deja el original reemplazado.",
                "$ echo texto | gzip > nota.gz\n$ zcat nota.gz\ntexto\n$ gunzip nota.gz",
                "gzip archivo crea archivo.gz y borra archivo. gzip -k lo conserva.",
            ),
            C(
                "bzip2 / xz",
                "Más compresión que gzip (más lentos). Extensiones .bz2 y .xz.",
                "$ xz -k grande.log\n$ unxz grande.log.xz",
                "xz -k conserva el original. En discos llenos, xz suele ganar espacio.",
            ),
            C(
                "zip / unzip",
                "Formato zip, el que entiende cualquier sistema.",
                "$ zip -r curso.zip curso/\n$ unzip -l curso.zip\n$ unzip curso.zip -d destino/",
                "zip -r incluye subcarpetas. unzip -l lista sin extraer.",
            ),
        ],
    ),
    (
        "Procesos",
        "Un proceso es un programa en ejecución. Tiene PID, dueño, CPU y memoria. Tú puedes listarlo o matarlo.",
        [
            C(
                "ps",
                "Fotografía de los procesos. aux es el juego de opciones más usado.",
                "$ ps aux | head\nUSER  PID %CPU %MEM COMMAND\nroot    1  0.0  0.2 /sbin/init",
                "ps -ef estilo System V. ps aux --sort=-%cpu los que más CPU comen.",
            ),
            C(
                "top / htop",
                "Monitor en vivo. top viene instalado; htop es más cómodo (apt install htop).",
                "$ top\n$ htop",
                "En top: P ordena por CPU, M por memoria, q sale, k mata un PID. htop se maneja con flechas.",
            ),
            C(
                "pstree",
                "Árbol de procesos: quién lanzó a quién.",
                "$ pstree -p | head\nsystemd(1)-+-sshd(812)-+-sshd(1402)\n           |-cron(790)",
                "Ayuda a entender servicios y shells hijas.",
            ),
            C(
                "pgrep / pidof",
                "Buscan el PID de un programa por nombre.",
                "$ pgrep -a sshd\n812 /usr/sbin/sshd -D\n$ pidof systemd\n1",
                "pgrep -a muestra también la línea de comando.",
            ),
            C(
                "kill / killall / pkill",
                "Envían una señal a un proceso. Por defecto TERM (cierre limpio). -9 es KILL (forzado).",
                "$ sleep 300 &\n[1] 4521\n$ kill 4521\n$ killall sleep\n$ pkill -f 'python app.py'",
                "Primero kill PID. Si no muere, kill -9 PID. -9 no deja guardar estado: úsalo de último recurso.",
            ),
            C(
                "nice / renice",
                "Prioridad de CPU. nice al lanzar; renice a un proceso ya en marcha. Rango -20 a 19.",
                "$ nice -n 15 gzip enorme.log\n$ renice 10 -p 4521",
                "Número alto = más educado = menos prioridad. Solo root baja de 0.",
            ),
            C(
                "nohup",
                "Lanza un comando que sobrevive al cierre de la terminal.",
                "$ nohup ./servidor.sh &\nnohup: ignoring input and appending output to 'nohup.out'",
                "La salida va a nohup.out. Alternativa moderna: systemd o tmux/screen.",
            ),
            C(
                "jobs / fg / bg / disown",
                "Control de trabajos de la shell actual. Un proceso en primer o segundo plano.",
                "$ sleep 200 &\n[1] 4600\n$ jobs\n[1]+  Running  sleep 200 &\n$ fg %1",
                "Ctrl+Z pausa. bg continúa en segundo plano. disown lo suelta de la shell.",
            ),
            C(
                "timeout",
                "Ejecuta un comando y lo mata si tarda más de N segundos.",
                "$ timeout 2 sleep 10\n$ echo $?\n124",
                "El código 124 significa que se agotó el tiempo. Ideal en scripts y cron.",
            ),
            C(
                "watch",
                "Repite un comando cada N segundos y refresca la pantalla.",
                "$ watch -n 1 df -h",
                "Ctrl+C sale. watch -d resalta lo que cambia.",
            ),
            C(
                "time",
                "Mide cuánto tarda un comando (real, usuario, sistema).",
                "$ time sleep 1\nreal    0m1.002s\nuser    0m0.000s\nsys     0m0.000s",
                "En bash, time es un builtin. /usr/bin/time da más detalle.",
            ),
            C(
                "lsof / fuser",
                "Qué proceso tiene abierto un archivo o un puerto.",
                "$ sudo lsof -i :22\nsshd  812 root  3u  IPv4  TCP *:ssh (LISTEN)\n$ sudo fuser -v /var/log/syslog",
                "Clásico: 'device busy' al desmontar → fuser o lsof te dicen quién lo usa.",
            ),
        ],
    ),
    (
        "Memoria, CPU y disco",
        "Cuando el sistema va lento, mira CPU (top), RAM (free) y espacio (df). En ese orden.",
        [
            C(
                "free",
                "Memoria RAM y swap. -h en unidades humanas.",
                "$ free -h\n       total  used  free  buff/cache  available\nMem:    7,6G  2,1G  1,4G        4,1G       5,2G\nSwap:   2,0G  0,0B  2,0G",
                "available es lo que importa, no free. Linux usa RAM libre como caché y eso es bueno.",
            ),
            C(
                "uptime",
                "Hace cuánto está encendida la máquina y la carga media (1, 5, 15 min).",
                "$ uptime\n 10:21:02 up 3 days,  4:11,  1 user,  load average: 0,15 0,10 0,08",
                "Carga 1.0 ≈ un núcleo al 100%. En un CPU de 4 núcleos, 4.0 es saturación.",
            ),
            C(
                "lscpu / nproc",
                "Información de la CPU y número de núcleos disponibles.",
                "$ nproc\n8\n$ lscpu | head",
                "nproc es el número que usan make -j y las herramientas de compilación.",
            ),
            C(
                "df",
                "Espacio de los sistemas de archivos montados. -h legible, -T tipo.",
                "$ df -h\nFilesystem  Size  Used Avail Use% Mounted on\n/dev/sda2    98G   42G   51G  46% /",
                "Si Use% llega a 100% en / el sistema se vuelve inestable. df -i muestra inodos.",
            ),
            C(
                "du",
                "Cuánto ocupa una carpeta. -sh resume; -h lista; --max-depth=1 un nivel.",
                "$ du -sh ~/practica\n1,2M    /home/usuario/practica\n$ du -h --max-depth=1 /var | sort -h",
                "El truco para encontrar qué llena el disco: du -h --max-depth=1 / | sort -h.",
            ),
            C(
                "vmstat",
                "Resumen de procesos, memoria, swap, IO y CPU en intervalos.",
                "$ vmstat 1 5",
                "Si si (swap in) no para de subir, te falta RAM y el sistema está paginando.",
            ),
            C(
                "sync",
                "Fuerza a escribir a disco todo lo que está en caché.",
                "$ sync",
                "Úsalo antes de extraer un USB a lo bruto o antes de un apagado de emergencia.",
            ),
            C(
                "dd",
                "Copia datos a bajo nivel, bloque a bloque. Potente y peligroso.",
                "$ dd if=/dev/zero of=prueba.bin bs=1M count=10\n10+0 records in\n10+0 records out",
                "if = origen, of = destino. Un of=/dev/sda mal puesto borra un disco. Revisa dos veces.",
            ),
        ],
    ),
    (
        "Discos, particiones y montaje",
        "Un disco se ve como /dev/sdX o /dev/nvme0n1. Hay que particionarlo, formatearlo y montarlo para usarlo.",
        [
            C(
                "lsblk",
                "Árbol de discos, particiones y puntos de montaje. El mapa más claro.",
                "$ lsblk\nNAME   SIZE TYPE MOUNTPOINTS\nsda    100G disk\n├─sda1   1G part /boot\n└─sda2  99G part /",
                "Empieza por lsblk antes de tocar fdisk. -f añade UUID y tipo de sistema de archivos.",
            ),
            C(
                "blkid",
                "UUID y tipo de cada partición. El UUID es lo que se pone en /etc/fstab.",
                "$ sudo blkid\n/dev/sda2: UUID=\"a1b2c3...\" TYPE=\"ext4\"",
                "Montar por UUID no se rompe si cambia el orden de los discos.",
            ),
            C(
                "mount / umount / findmnt",
                "Monta o desmonta un sistema de archivos. findmnt lista lo montado con detalle.",
                "$ sudo mkdir -p /mnt/usb\n$ sudo mount /dev/sdb1 /mnt/usb\n$ findmnt /mnt/usb\n$ sudo umount /mnt/usb",
                "Si umount dice busy, cierra procesos con fuser -km /mnt/usb (con cuidado).",
            ),
            C(
                "fdisk / parted",
                "Editores de tabla de particiones. fdisk es clásico; parted aguanta discos enormes y GPT.",
                "$ sudo fdisk -l\nDisk /dev/sda: 100 GiB\n$ sudo parted -l",
                "Solo lista con -l mientras estudias. Crear o borrar particiones destruye datos.",
            ),
            C(
                "mkfs",
                "Crea un sistema de archivos (formatea). Elige el tipo: ext4, xfs, vfat...",
                "$ sudo mkfs.ext4 /dev/sdb1",
                "Formatear borra la partición. Confirma el nombre con lsblk tres veces.",
            ),
            C(
                "fsck",
                "Comprueba y repara un sistema de archivos. Debe estar desmontado.",
                "$ sudo umount /dev/sdb1\n$ sudo fsck /dev/sdb1",
                "Ubuntu lo lanza solo si un disco no se desmontó bien. No lo ejecutes sobre / en caliente.",
            ),
        ],
    ),
    (
        "Red",
        "Primero comprueba tu IP, luego si hay eco (ping), luego DNS y por último puertos.",
        [
            C(
                "ip",
                "Herramienta moderna de red (sustituye ifconfig). ip a lista interfaces.",
                "$ ip a\n2: wlan0: <BROADCAST,UP> mtu 1500\n    inet 192.168.1.40/24",
                "ip a direcciones, ip r rutas, ip link set wlan0 up/down. ifconfig es legado.",
            ),
            C(
                "hostname / hostnamectl",
                "Nombre de la máquina. hostnamectl también muestra el sistema operativo.",
                "$ hostname\nubuntu-pc\n$ hostnamectl",
                "sudo hostnamectl set-hostname nuevo-nombre cambia el nombre de forma persistente.",
            ),
            C(
                "ping",
                "Envía paquetes ICMP para ver si un host responde y con qué latencia.",
                "$ ping -c 4 8.8.8.8\n64 bytes from 8.8.8.8: icmp_seq=1 ttl=117 time=18.2 ms",
                "-c 4 manda solo 4. Si 8.8.8.8 responde pero ubuntu.com no, el problema es DNS.",
            ),
            C(
                "traceroute / tracepath",
                "Muestra los saltos hasta el destino. tracepath no necesita root.",
                "$ tracepath ubuntu.com",
                "Sirve para ver en qué router se pierde la conexión.",
            ),
            C(
                "ss / netstat",
                "Puertos y conexiones. ss es el moderno; netstat puede no estar instalado.",
                "$ ss -tuln\nNetid  State   Local Address:Port\ntcp    LISTEN  0.0.0.0:22",
                "-t tcp, -u udp, -l listen, -n numérico (sin resolver nombres). -p añade el proceso (sudo).",
            ),
            C(
                "dig / host / nslookup",
                "Consultas DNS. dig es el más claro; host es rápido; nslookup es el clásico.",
                "$ host ubuntu.com\nubuntu.com has address 185.125.190.20\n$ dig +short ubuntu.com",
                "Si ping a una IP funciona y host falla, tu DNS está mal (mira /etc/resolv.conf).",
            ),
            C(
                "curl",
                "Habla HTTP y muchos otros protocolos. Ideal para probar APIs y cabeceras.",
                "$ curl -I https://ubuntu.com\nHTTP/2 200\nserver: nginx",
                "-I solo cabeceras, -O descarga, -L sigue redirecciones, -s silencioso.",
            ),
            C(
                "wget",
                "Descarga archivos. Más 'a lo descargador' que curl.",
                "$ wget https://example.com/archivo.zip",
                "wget -c reanuda una descarga cortada. -P /tmp elige destino.",
            ),
            C(
                "nc (netcat)",
                "Navaja suiza TCP/UDP: probar un puerto, escuchar o mandar un archivo.",
                "$ nc -zv 127.0.0.1 22\nConnection to 127.0.0.1 22 port [tcp/ssh] succeeded!",
                "-z escanea sin mandar datos, -v verbose. No lo uses para escanear redes ajenas.",
            ),
            C(
                "nmcli",
                "NetworkManager por terminal. Wi-Fi, Ethernet y perfiles en Ubuntu de escritorio.",
                "$ nmcli device status\nwlan0  wifi  connected  MiWifi\n$ nmcli connection show",
                "nmcli device wifi list ve redes. nmcli radio wifi off apaga el Wi-Fi.",
            ),
        ],
    ),
    (
        "Acceso remoto y transferencia",
        "ssh entra a otra máquina. scp y rsync copian archivos. Nunca compartas tu clave privada.",
        [
            C(
                "ssh",
                "Abre una sesión cifrada en otro equipo. Es el acceso estándar a servidores Linux.",
                "$ ssh usuario@192.168.1.50\n$ ssh -p 2222 usuario@servidor.ejemplo.com",
                "Primera vez te pide confirmar la huella. ssh-copy-id instala tu clave para no usar contraseña.",
            ),
            C(
                "scp",
                "Copia archivos por SSH. Sintaxis origen destino, uno de los dos es remoto.",
                "$ scp notas.txt usuario@servidor:/tmp/\n$ scp usuario@servidor:/var/log/syslog ./",
                "scp -r copia carpetas. Cada vez abre conexión nueva; para sync grande usa rsync.",
            ),
            C(
                "sftp",
                "FTP sobre SSH, interactivo: ls, put, get, cd en el servidor remoto.",
                "$ sftp usuario@servidor\nsftp> put notas.txt\nsftp> ls\nsftp> bye",
                "Más cómodo que scp si vas a mover muchos archivos a mano.",
            ),
            C(
                "rsync por SSH",
                "Sincroniza una carpeta contra un servidor. Solo manda diferencias.",
                "$ rsync -avz -e ssh curso/ usuario@servidor:~/curso/",
                "-z comprime en tránsito. --progress muestra avance. --dry-run simula.",
            ),
            C(
                "ssh-keygen",
                "Crea un par de claves SSH. La pública se copia al servidor; la privada no sale de tu PC.",
                "$ ssh-keygen -t ed25519 -C 'mi-correo@ejemplo.com'\n$ ls ~/.ssh\nid_ed25519  id_ed25519.pub",
                "id_ed25519 es privada (600). .pub es pública y se puede compartir.",
            ),
        ],
    ),
    (
        "Paquetes en Ubuntu (apt y dpkg)",
        "Ubuntu instala software con apt, que usa el formato .deb de Debian. Snap es un canal aparte.",
        [
            C(
                "apt update",
                "Actualiza la lista de paquetes disponibles. No instala nada. Hazlo antes de instalar.",
                "$ sudo apt update\nHit:1 http://archive.ubuntu.com/ubuntu noble InRelease",
                "Si omites update, apt puede intentar versiones viejas o no encontrar un paquete nuevo.",
            ),
            C(
                "apt upgrade / full-upgrade",
                "Instala versiones nuevas de lo que ya tienes. full-upgrade puede quitar paquetes si hace falta.",
                "$ sudo apt upgrade\n$ sudo apt full-upgrade",
                "upgrade es el día a día. full-upgrade antes de un salto grande de versión.",
            ),
            C(
                "apt install / remove / purge / autoremove",
                "Instala o quita paquetes. purge borra también la configuración. autoremove limpia dependencias huérfanas.",
                "$ sudo apt install htop\n$ sudo apt remove htop\n$ sudo apt purge htop\n$ sudo apt autoremove",
                "adduser vs apt: adduser crea cuentas, apt instala programas. No los mezcles.",
            ),
            C(
                "apt search / show / list",
                "Busca paquetes, ve detalles y lista lo instalado.",
                "$ apt search htop\n$ apt show htop\n$ apt list --installed | grep htop",
                "search y show no necesitan sudo.",
            ),
            C(
                "apt-get / apt-cache",
                "Herramientas clásicas. En scripts se suele preferir apt-get por su salida estable.",
                "$ sudo apt-get update\n$ apt-cache policy htop",
                "En la terminal interactiva usa apt. En cron y recetas, apt-get.",
            ),
            C(
                "dpkg",
                "Gestor de bajo nivel de .deb. apt lo llama por debajo.",
                "$ dpkg -l | head\n$ sudo dpkg -i paquete.deb\n$ dpkg -L htop",
                "-i instala un .deb local. Si faltan dependencias: sudo apt -f install.",
            ),
            C(
                "add-apt-repository",
                "Añade un PPA o un repositorio extra. Paquete: software-properties-common.",
                "$ sudo add-apt-repository ppa:ejemplo/ppa\n$ sudo apt update",
                "Solo añade PPA de fuentes que te fíes. Luego se instala con apt install.",
            ),
            C(
                "snap",
                "Paquetes confinados de Canonical, independientes de apt.",
                "$ snap list\n$ sudo snap install vlc\n$ snap find vlc",
                "Algunas apps de Ubuntu (Firefox en varias versiones) llegan por snap, no por apt.",
            ),
        ],
    ),
    (
        "systemd: servicios y arranque",
        "systemd es el PID 1 de Ubuntu. Arranca servicios, los reinicia y guarda sus logs.",
        [
            C(
                "systemctl status / start / stop / restart",
                "Consulta y controla un servicio (unidad).",
                "$ systemctl status ssh\n$ sudo systemctl restart ssh\n$ sudo systemctl stop ssh\n$ sudo systemctl start ssh",
                "status no necesita sudo. Los otros sí, porque cambian el sistema.",
            ),
            C(
                "systemctl enable / disable / is-enabled",
                "enable hace que el servicio arranque con el sistema. disable lo quita del arranque.",
                "$ systemctl is-enabled ssh\nenabled\n$ sudo systemctl enable nginx\n$ sudo systemctl disable nginx",
                "enable no lo inicia ahora (usa start). disable no lo para ahora (usa stop).",
            ),
            C(
                "systemctl list-units",
                "Lista unidades cargadas. --type=service solo servicios. --failed los que rompieron.",
                "$ systemctl list-units --type=service --state=running\n$ systemctl --failed",
                "Si algo no arranca, --failed es el primer sitio que mirar.",
            ),
            C(
                "journalctl",
                "Lee el diario de systemd. Es el log unificado del sistema.",
                "$ journalctl -u ssh -n 20\n$ journalctl -xe\n$ journalctl -f",
                "-u unidad, -n últimas N, -f sigue, -xe errores recientes, --since today. sudo a veces hace falta.",
            ),
            C(
                "hostnamectl / timedatectl / localectl",
                "Configuran nombre, hora/zona y teclado/locale de forma persistente.",
                "$ timedatectl\n               Local time: mar 2026-09-22 10:30:00\n$ timedatectl list-timezones | grep Madrid\n$ sudo timedatectl set-timezone Europe/Madrid",
                "localectl set-keymap es para consola. En escritorio el teclado lo lleva GNOME.",
            ),
            C(
                "service",
                "Envuelta antigua que en Ubuntu acaba llamando a systemctl.",
                "$ sudo service ssh status",
                "Funciona, pero aprende systemctl: es la interfaz real.",
            ),
        ],
    ),
    (
        "Registros (logs)",
        "Cuando algo falla, los logs cuentan la historia. En Ubuntu moderno vive sobre todo en journalctl.",
        [
            C(
                "dmesg",
                "Mensajes del kernel: hardware, USB, discos, errores de drivers.",
                "$ dmesg | tail\n$ sudo dmesg -T | grep -i error",
                "-T traduce marcas de tiempo a fecha humana. Suele requerir sudo.",
            ),
            C(
                "logger",
                "Escribe una línea en el log del sistema desde un script.",
                "$ logger 'backup terminado'\n$ journalctl -n 5 | grep backup",
                "Así tus propios scripts dejan rastro junto al resto del sistema.",
            ),
            C(
                "Archivos clásicos en /var/log",
                "Aún existen logs en texto: syslog, auth.log, dpkg.log, apt/history.log.",
                "$ sudo tail -n 20 /var/log/auth.log\n$ less /var/log/dpkg.log",
                "auth.log registra sudo, ssh e inicios de sesión. Léelo si sospechas un acceso.",
            ),
        ],
    ),
    (
        "Fecha, hora y espera",
        "La hora del sistema importa para cron, certificados y logs. Aprende a leerla y a pausar un script.",
        [
            C(
                "date",
                "Fecha y hora actuales. Acepta formato con + y también puede fijar la hora (root).",
                "$ date\nmar 22 sep 2026 10:35:01 CDT\n$ date +%Y-%m-%d\n2026-09-22",
                "+%H:%M hora. date -d 'yesterday' interpreta fechas. No cambies la hora si usas NTP.",
            ),
            C(
                "cal",
                "Calendario en texto.",
                "$ cal\n   Septiembre 2026\nDo Lu Ma Mi Ju Vi Sá\n       1  2  3  4  5",
                "cal 2026 muestra el año. ncal -w añade número de semana.",
            ),
            C(
                "sleep",
                "Pausa N segundos (también 0.5 o 2m, 1h).",
                "$ sleep 2 && echo listo\nlisto",
                "Pieza básica de cualquier script que espera a otro servicio.",
            ),
            C(
                "hwclock",
                "Lee o sincroniza el reloj de hardware (la pila de la placa).",
                "$ sudo hwclock --verbose",
                "timedatectl es más amable. hwclock es el nivel bajo.",
            ),
        ],
    ),
    (
        "Shell, variables e historial",
        "Bash (la shell de Ubuntu) no es solo un sitio para pegar comandos: tiene variables, alias y memoria.",
        [
            C(
                "echo $VARIABLE / printenv / env",
                "Las variables de entorno configuran programas (PATH, HOME, LANG, USER).",
                "$ echo $HOME\n/home/usuario\n$ echo $PATH\n/usr/local/bin:/usr/bin:/bin\n$ printenv USER",
                "PATH es la lista de carpetas donde bash busca comandos. Si 'no encuentra' un programa, mira PATH.",
            ),
            C(
                "export / unset",
                "export hace visible una variable a los programas hijos. unset la borra.",
                "$ export SALUDO=hola\n$ bash -c 'echo $SALUDO'\nhola\n$ unset SALUDO",
                "Sin export, la variable solo existe en esa shell. Para que dure, ve a ~/.bashrc.",
            ),
            C(
                "alias / unalias",
                "Atajo de un comando largo. Ubuntu ya trae alias de ls con color.",
                "$ alias ll='ls -lah'\n$ ll\n$ unalias ll",
                "Ponlos en ~/.bashrc para que sobrevivan. alias solo lista los actuales.",
            ),
            C(
                "source / .",
                "Ejecuta un archivo en la shell actual (no en una subshell). Recarga configuración.",
                "$ echo \"export CURSO=linux\" >> ~/.bashrc\n$ source ~/.bashrc\n$ echo $CURSO\nlinux",
                "Después de editar .bashrc, source ~/.bashrc. Abrir otra terminal también vale.",
            ),
            C(
                "history",
                "Lista los comandos que ya ejecutaste. Se guarda en ~/.bash_history.",
                "$ history | tail -n 5\n  210  ls\n  211  cd ~/practica\n$ history | grep apt",
                "↑ y ↓ recorren el historial. Ctrl+R busca incremental. !210 repite el número 210.",
            ),
            C(
                "clear / reset",
                "clear limpia la pantalla. reset repara una terminal que se puso rara (caracteres locos).",
                "$ clear\n$ reset",
                "Ctrl+L también limpia. reset cuando un comando binario ensució la consola.",
            ),
            C(
                "locale",
                "Idioma y formato de fechas, números y moneda.",
                "$ locale\nLANG=es_MX.UTF-8",
                "Si ves acentos rotos, casi siempre es un locale mal configurado.",
            ),
            C(
                "set / shopt",
                "Opciones de bash. set -e aborta un script al primer error. set -x depura.",
                "$ set -x\n$ echo hola\n+ echo hola\nhola\n$ set +x",
                "Al inicio de un script serio: set -euo pipefail.",
            ),
        ],
    ),
    (
        "Redirecciones, tuberías y operadores",
        "Esto no son comandos: son el pegamento. Si los dominas, dejas de ser principiante.",
        [
            C(
                "|  tubería (pipe)",
                "La salida de la izquierda es la entrada de la derecha. Encadena filtros.",
                "$ ls /etc | grep ssh\nssh_config\nsshd_config",
                "Piensa en una cadena de montaje: cada comando hace una sola cosa y pasa el resultado.",
            ),
            C(
                ">  y  >>",
                "> escribe a un archivo y borra lo que había. >> añade al final.",
                "$ echo uno > salida.txt\n$ echo dos >> salida.txt\n$ cat salida.txt\nuno\ndos",
                "echo hola > archivo.txt con un solo > vacía el archivo. Es el accidente más común.",
            ),
            C(
                "<  entrada desde archivo",
                "El comando lee el archivo como si lo hubieras tecleado.",
                "$ wc -l < veinte.txt\n20",
                "Es distinto de wc -l veinte.txt: aquí wc no recibe el nombre, solo el contenido.",
            ),
            C(
                "2>  y  2>&1",
                "2 es la salida de error. 2> errores.txt la guarda. 2>&1 junta error y salida normal.",
                "$ ls no-existe 2> errores.txt\n$ cat errores.txt\nls: no se puede acceder a 'no-existe': No existe el archivo",
                "comando > todo.txt 2>&1 guarda las dos salidas. /dev/null es un agujero negro.",
            ),
            C(
                "&&  y  ||",
                "&& ejecuta lo siguiente solo si lo anterior salió bien. || solo si falló.",
                "$ mkdir -p demo && cd demo && pwd\n/home/usuario/practica/demo\n$ ls falta || echo 'no estaba'",
                "No uses ; si el segundo paso depende del primero. mkdir x; cd x es más frágil que &&.",
            ),
            C(
                ";  y  &",
                "; separa comandos y los corre igual. & lanza el de la izquierda en segundo plano.",
                "$ date; echo hola; pwd\n$ sleep 30 &",
                "& devuelve el prompt al momento. Mira jobs para verlo. No lo uses con comandos interactivos.",
            ),
            C(
                "$( )  y  ` `",
                "Sustitución de comando: se ejecuta lo de dentro y se pega el resultado.",
                "$ echo \"Hoy es $(date +%Y-%m-%d)\"\nHoy es 2026-09-22",
                "$( ) es la forma moderna. Evita los acentos graves ` ` en scripts nuevos.",
            ),
            C(
                "Comodines  * ? [ ]",
                "La shell expande patrones de nombres de archivo antes de llamar al comando.",
                "$ ls *.txt\nfrutas.txt  notas.txt\n$ ls dia?.txt\n$ ls archivo[12].txt",
                "* cualquiera, ? un carácter, [12] 1 o 2. Entre comillas no se expanden: ls '*.txt'.",
            ),
        ],
    ),
    (
        "Tareas programadas",
        "cron ejecuta algo en un horario. at lo ejecuta una vez. systemd timers es la vía moderna.",
        [
            C(
                "crontab",
                "Edita la tabla cron del usuario. Cada línea: minuto hora día mes día-semana comando.",
                "$ crontab -e\n# Cada día a las 07:30\n30 7 * * * /usr/bin/date >> /home/usuario/practica/hora.txt\n$ crontab -l",
                "Usa rutas absolutas. cron no carga tu .bashrc ni el mismo PATH de la terminal.",
            ),
            C(
                "at",
                "Programa un comando para que corra una vez, más tarde.",
                "$ echo 'echo hola > ~/practica/at.txt' | at now + 2 minutes\n$ atq",
                "atq lista, atrm borra. El servicio atd debe estar activo.",
            ),
            C(
                "systemd-run / timers",
                "Alternative moderna a cron, integrada con journalctl.",
                "$ systemd-run --on-active=10s echo hola\n$ systemctl list-timers",
                "Cuando un servicio ya es de systemd, un timer encaja mejor que crontab.",
            ),
        ],
    ),
    (
        "Apagar, reiniciar y salir",
        "Cierra siempre de forma limpia: los sistemas de archivos se pueden corromper si cortas la luz.",
        [
            C(
                "shutdown",
                "Apaga o reinicia avisando a los usuarios. -h apaga, -r reinicia, now es inmediato.",
                "$ sudo shutdown -h now\n$ sudo shutdown -r +10 'Reinicio en 10 minutos'\n$ sudo shutdown -c",
                "-c cancela un apagado programado. Es la forma educada en un servidor con más gente.",
            ),
            C(
                "reboot / poweroff / halt",
                "Atajos. En Ubuntu acaban hablando con systemd.",
                "$ sudo reboot\n$ sudo poweroff",
                "reboot = shutdown -r now. poweroff = shutdown -h now.",
            ),
            C(
                "logout / exit",
                "Cierran la sesión de terminal. exit también termina un script.",
                "$ exit",
                "Ctrl+D es el atajo de exit cuando la línea está vacía.",
            ),
        ],
    ),
    (
        "Información del sistema y hardware",
        "Cuando te sientas en una máquina nueva, estos comandos te dicen qué es y qué tiene dentro.",
        [
            C(
                "uname",
                "Núcleo y arquitectura. -a es el resumen completo.",
                "$ uname -a\nLinux ubuntu-pc 6.8.0-40-generic #40-Ubuntu SMP x86_64 GNU/Linux",
                "-s kernel, -r versión, -m arquitectura (x86_64, aarch64).",
            ),
            C(
                "lsb_release / os-release",
                "Qué distribución y versión de Ubuntu estás usando.",
                "$ lsb_release -a\nDescription:  Ubuntu 24.04.1 LTS\n$ cat /etc/os-release",
                "Imprescindible antes de copiar instrucciones de internet: 22.04 y 24.04 no son iguales.",
            ),
            C(
                "lshw / dmidecode",
                "Inventario de hardware. dmidecode lee la BIOS/UEFI (DMI).",
                "$ sudo lshw -short\n$ sudo dmidecode -t memory",
                "Suelen necesitar sudo. lshw -class disk lista discos con modelo.",
            ),
            C(
                "lspci / lsusb",
                "Dispositivos PCI (gráfica, red, NVMe) y USB conectados.",
                "$ lspci\n00:02.0 VGA compatible controller: ...\n$ lsusb",
                "Si un USB no aparece, prueba otro puerto y mira dmesg.",
            ),
            C(
                "lsmod / modprobe",
                "Módulos del kernel (drivers cargados). modprobe carga o descarga uno.",
                "$ lsmod | head\n$ sudo modprobe nombre_modulo\n$ sudo modprobe -r nombre_modulo",
                "No descargues módulos al azar: puedes perder red, disco o gráficos.",
            ),
            C(
                "uname + who + df (foto rápida)",
                "Rutina de 20 segundos al sentarte en un servidor desconocido.",
                "$ uname -a && whoami && df -h && ip -br a",
                "Kernel, tu usuario, disco y red. Con eso ya no estás ciego.",
            ),
        ],
    ),
    (
        "Ayuda e información de comandos",
        "Nadie se sabe todas las opciones. Lo profesional es saber dónde mirar.",
        [
            C(
                "man",
                "Manual oficial. Secciones: 1 usuario, 5 archivos de config, 8 administración.",
                "$ man chmod\n$ man 5 passwd\n$ man man",
                "/palabra busca, n siguiente, q sale. man -k disco busca en las descripciones.",
            ),
            C(
                "info",
                "Manuales GNU, a veces más largos que man, con hiperenlaces.",
                "$ info coreutils",
                "Flechas para moverte, q sale. Menos usado, pero existe.",
            ),
            C(
                "help",
                "Ayuda de los builtins de bash (cd, alias, history, [, read...).",
                "$ help cd\n$ help history",
                "man cd puede mostrarte el de otra shell. help es la verdad de bash.",
            ),
            C(
                "whatis / apropos",
                "whatis una línea de resumen. apropos busca esa palabra en todos los man.",
                "$ whatis ls\nls (1)  - list directory contents\n$ apropos 'disk space'",
                "apropos es man -k. Ideal cuando no recuerdas el nombre del comando.",
            ),
            C(
                "tldr",
                "Ejemplos cortos y humanos. Se instala aparte y complementa a man.",
                "$ sudo apt install tldr\n$ tldr tar",
                "Cuando man es un muro de texto, tldr te da los 5 usos reales.",
            ),
        ],
    ),
    (
        "Otros coreutils que debes reconocer",
        "Forman parte de GNU coreutils. Aparecen en exámenes, scripts ajenos y foros. No hace falta memorizarlos todos el primer mes.",
        [
            C(
                "true / false",
                "No hacen nada útil por sí solos: true sale con código 0, false con 1.",
                "$ true && echo ok\nok\n$ false || echo fallo\nfallo",
                "Se usan en scripts y en condiciones. echo $? muestra el código del último comando.",
            ),
            C(
                "yes (recordatorio)",
                "Ya lo viste: genera 'y' infinito. Aquí el uso típico de confirmación.",
                "$ yes | apt list --installed | head",
                "En instalaciones desatendidas a veces verás yes | comando.",
            ),
            C(
                "factor",
                "Descompone un número en factores primos.",
                "$ factor 84\n84: 2 2 3 7",
                "Ejemplo de utilidad GNU pequeña. Buen juguete, poco uso de administración.",
            ),
            C(
                "numfmt",
                "Convierte números a unidades humanas o al revés.",
                "$ echo 1048576 | numfmt --to=iec\n1.0M",
                "Muy útil al formatear salidas de du o de scripts de cuotas.",
            ),
            C(
                "basename / dirname (en scripts)",
                "Patrón típico para localizar la carpeta del propio script.",
                "$ archivo=/home/usuario/practica/a.txt\n$ echo \"$(dirname $archivo)/copia-$(basename $archivo)\"\n/home/usuario/practica/copia-a.txt",
                "En scripts robustos se citan las variables: \"$(dirname \"$archivo\")\".",
            ),
            C(
                "install -d",
                "Crea carpetas como mkdir -p, con permisos explícitos.",
                "$ install -d -m 750 ~/practica/privado\n$ ls -ld ~/practica/privado\ndrwxr-x--- 2 usuario usuario 4096 ...",
                "Lo verás en Makefiles y recetas de empaquetado.",
            ),
            C(
                "pr / fmt / fold",
                "Dan formato a texto: páginas, relleno de párrafos y ajuste de ancho.",
                "$ echo 'un texto bastante largo para la terminal' | fold -w 20\nun texto bastante l\nargo para la termin\nal",
                "fmt rellena párrafos. pr pagina para imprimir. Hoy se usan poco, pero existen.",
            ),
            C(
                "expand / unexpand",
                "Pasan tabuladores a espacios y al revés.",
                "$ printf 'a\\tb\\n' | expand -t 4\na   b",
                "Útil al limpiar código o archivos TSV mal formateados.",
            ),
            C(
                "join",
                "Une líneas de dos archivos por un campo común. Ambos deben estar ordenados.",
                "$ echo -e '1 ana\\n2 luis' > p.txt\n$ echo -e '1 9\\n2 8' > n.txt\n$ join p.txt n.txt\n1 ana 9\n2 luis 8",
                "Si no están ordenados, join se equivoca. Ordénalos antes con sort.",
            ),
            C(
                "tsort",
                "Orden topológico: resuelve dependencias (A antes que B).",
                "$ printf 'a b\\nb c\\n' | tsort\na\nb\nc",
                "Aparece en recetas de compilación y grafos de paquetes.",
            ),
            C(
                "uniq (con sort)",
                "Patrón de estudio: contar qué se repite más.",
                "$ printf 'error\\ninfo\\nerror\\nerror\\ninfo\\n' | sort | uniq -c | sort -nr\n      3 error\n      2 info",
                "sort | uniq -c | sort -nr es de las tuberías más útiles que existen.",
            ),
            C(
                "stdbuf",
                "Cambia el buffer de un comando. Evita que la tubería 'se quede muda' hasta llenarse.",
                "$ stdbuf -oL grep error grande.log | head",
                "-oL salida línea a línea. Lo necesitas cuando tail -f | grep no imprime al momento.",
            ),
            C(
                "timeout + yes (patrón de examen)",
                "Combina dos coreutils para no dejar un comando infinito suelto.",
                "$ timeout 1 yes | wc -l\n185492",
                "yes solo no para. timeout lo corta. El número de líneas depende de lo rápido que sea el PC.",
            ),
        ],
    ),
    (
        "Plan de 7 días",
        "No intentes memorizar la guía de un tirón. Un capítulo bien practicado vale más que diez leídos.",
        [
            C(
                "Día 1 — Navegar",
                "pwd, ls, cd, tree. Entra y sal de /etc, /var/log y tu home hasta hacerlo sin mirar.",
                "$ mkdir -p ~/practica && cd ~/practica\n$ pwd\n$ ls -la\n$ cd /etc && ls && cd -",
                "Meta: llegar a cualquier ruta y volver a ~/practica en menos de 10 segundos.",
            ),
            C(
                "Día 2 — Archivos",
                "touch, mkdir, cp, mv, rm, cat. Crea, copia, renombra y borra solo dentro de ~/practica.",
                "$ touch notas.txt\n$ mkdir backup\n$ cp notas.txt backup/\n$ mv backup/notas.txt backup/copia.txt",
                "Meta: explicar la diferencia entre cp y mv, y entre > y >>.",
            ),
            C(
                "Día 3 — Leer y buscar",
                "less, head, tail, grep, find. Busca texto en tus archivos y en /var/log.",
                "$ echo -e 'manzana\\npera' > frutas.txt\n$ grep pera frutas.txt\n$ find ~/practica -name '*.txt'",
                "Meta: filtrar la salida de ls o ps con grep sin dudar.",
            ),
            C(
                "Día 4 — Permisos y usuarios",
                "ls -l, chmod, chown, whoami, id, sudo. Lee rwx y cambia 644 / 755.",
                "$ touch secreto.txt\n$ chmod 600 secreto.txt\n$ ls -l secreto.txt\n$ whoami && id",
                "Meta: decir en voz alta qué significa rwxr-xr-x y por qué 777 es mala idea.",
            ),
            C(
                "Día 5 — Procesos y recursos",
                "ps, top, kill, free, df, du. Abre sleep 300 y mátalo por PID.",
                "$ sleep 300 &\n$ ps aux | grep sleep\n$ free -h && df -h",
                "Meta: identificar un proceso, su PID y terminarlo con kill (sin -9).",
            ),
            C(
                "Día 6 — Paquetes Ubuntu",
                "apt update, search, install, show, remove. Instala htop y tree, úsalos y decide si los dejas.",
                "$ sudo apt update\n$ apt search htop\n$ sudo apt install htop tree\n$ htop",
                "Meta: instalar un programa y saber cómo desinstalarlo después.",
            ),
            C(
                "Día 7 — Red, tuberías y examen",
                "ip, ping, curl, |, >, >>. Encadena tres comandos y guarda el resultado.",
                "$ ip -br a\n$ ping -c 2 8.8.8.8\n$ ls -la | grep txt > resultado.txt\n$ cat resultado.txt",
                "Examen: tres .txt, grep de una palabra, resultado guardado. Si lo haces sin mirar, ya no eres cero.",
            ),
        ],
    ),
    (
        "Errores comunes y reglas de oro",
        "Casi todos los sustos de principiantes caben en esta lista. Léela dos veces.",
        [
            C(
                "rm -rf fuera de práctica",
                "Borra sin preguntar y no hay papelera. Un espacio mal puesto cambia el destino.",
                "$ # PELIGRO, no lo ejecutes:\n$ # rm -rf / home/usuario/tmp\n$ #           ^ ese espacio borra /",
                "Regla: escribe primero la ruta, revisa con ls, y solo entonces pon rm. Nunca rm -rf * en /.",
            ),
            C(
                "Un solo > en vez de >>",
                "Pisa el archivo y deja solo la última escritura.",
                "$ echo importante > bitacora.txt\n$ echo otra cosa > bitacora.txt\n$ cat bitacora.txt\notra cosa",
                "Si quieres histórico, usa >> o tee -a. Si vas a usar >, mira el nombre dos veces.",
            ),
            C(
                "chmod 777",
                "Todo el mundo puede leer, escribir y ejecutar. En un servidor es una invitación.",
                "$ # Evita esto:\n$ # chmod 777 -R /var/www\n$ chmod 755 script.sh\n$ chmod 644 archivo.txt",
                "Scripts: 755. Archivos de datos: 644. Secretos: 600. Carpetas privadas: 700.",
            ),
            C(
                "sudo por costumbre",
                "sudo no es un prefijo mágico. Te deja romper el sistema más rápido.",
                "$ # Primero sin sudo. Si el error es permiso, entonces sudo.\n$ systemctl status ssh\n$ sudo systemctl restart ssh",
                "Si un comando de usuario funciona, no lo conviertas en root 'por si acaso'.",
            ),
            C(
                "cd a una ruta y olvidar dónde estás",
                "Borras o editas en el sitio equivocado porque no miraste pwd.",
                "$ pwd\n$ ls\n$ cd ~/practica",
                "Antes de rm, chmod o > : pwd && ls. El hábito vale más que cualquier alias.",
            ),
            C(
                "Espacios en nombres de archivo",
                "La shell parte por espacios. Sin comillas, un nombre se convierte en varios argumentos.",
                "$ mkdir 'mi carpeta'\n$ ls mi carpeta\nls: no se puede acceder a 'mi': No existe\n$ ls 'mi carpeta'",
                "Usa comillas o escapa: mi\\ carpeta. En scripts, siempre \"$variable\".",
            ),
            C(
                "PATH y 'comando no encontrado'",
                "El programa puede estar instalado y aun así no verse si su carpeta no está en PATH.",
                "$ type codigo\n$ echo $PATH\n$ ls /usr/bin/codigo /usr/local/bin/codigo",
                "which y type diagnostican. No copies export PATH de internet sin entender qué quitas.",
            ),
        ],
    ),
]


class GuidePDF(FPDF):
    def __init__(self):
        super().__init__(format="A4", unit="mm")
        self.set_auto_page_break(auto=True, margin=18)
        self.set_margins(16, 18, 16)
        fonts = prepare_fonts()
        self.add_font("Body", "", str(fonts / "Body.ttf"))
        self.add_font("Body", "B", str(fonts / "Body-Bold.ttf"))
        self.add_font("Body", "I", str(fonts / "Body-Italic.ttf"))
        self.add_font("Code", "", str(fonts / "Code.ttf"))
        self.add_font("Code", "B", str(fonts / "Code-Bold.ttf"))
        self.set_title("Guía de Estudio: Comandos de Linux (Ubuntu)")
        self.set_author("Guía de estudio Linux")
        self.set_creator("generar_guia_linux.py")
        self.set_lang("es")
        self.chapter_no = 0
        self.in_cover = True
        self.in_toc = False

    def header(self):
        if self.in_cover or self.in_toc:
            return
        self.set_font("Body", "", 8)
        self.set_text_color(*MUTED)
        self.cell(0, 6, "Guía de estudio  ·  Comandos de Linux (Ubuntu)", align="L")
        self.set_draw_color(*ORANGE)
        self.set_line_width(0.5)
        self.line(16, 12, 194, 12)
        self.ln(8)

    def footer(self):
        if self.page_no() == 1:
            return
        self.set_y(-12)
        self.set_font("Body", "", 8)
        self.set_text_color(*MUTED)
        self.cell(0, 8, f"{self.page_no()}", align="C")

    def cover(self):
        self.in_cover = True
        self.add_page()
        self.set_fill_color(*AUBERGINE)
        self.rect(0, 0, 210, 297, "F")
        self.set_fill_color(*ORANGE)
        self.rect(0, 0, 8, 297, "F")
        self.set_xy(28, 58)
        self.set_font("Body", "", 13)
        self.set_text_color(233, 180, 160)
        self.cell(0, 8, "UBUNTU  ·  LINUX  ·  TERMINAL")
        self.set_xy(28, 78)
        self.set_font("Body", "B", 36)
        self.set_text_color(*WHITE)
        self.multi_cell(160, 14, "Guía de estudio")
        self.set_xy(28, 110)
        self.set_font("Body", "B", 22)
        self.set_text_color(*ORANGE)
        self.multi_cell(160, 10, "Comandos de Linux")
        self.set_xy(28, 138)
        self.set_font("Body", "", 12)
        self.set_text_color(220, 210, 205)
        self.multi_cell(
            155,
            6.5,
            "Cada comando con su función y un ejemplo de cómo funciona.\n"
            "Pensada para practicar en Ubuntu, de cero a administración diaria.",
        )
        self.set_fill_color(*ORANGE)
        self.rect(28, 178, 42, 1.2, "F")
        self.set_xy(28, 188)
        self.set_font("Body", "", 10)
        self.set_text_color(200, 190, 185)
        self.multi_cell(
            155,
            5.5,
            "Incluye navegación, archivos, permisos, usuarios, búsqueda, texto,\n"
            "procesos, disco, red, apt, systemd, logs, cron y operadores del shell.\n"
            "Más un plan de 7 días y una lista de errores que rompen sistemas.",
        )
        self.set_xy(28, 260)
        self.set_font("Body", "", 9)
        self.set_text_color(160, 150, 148)
        self.cell(0, 5, "Estudia en ~/practica   ·   Consulta man y --help   ·   2026")
        self.in_cover = False

    def toc_page(self):
        self.in_toc = True
        self.add_page()
        self.set_font("Body", "B", 22)
        self.set_text_color(*AUBERGINE)
        self.cell(0, 12, "Índice", new_x="LMARGIN", new_y="NEXT")
        self.set_fill_color(*ORANGE)
        self.rect(16, self.get_y(), 28, 1.1, "F")
        self.ln(8)
        for i, (title, _intro, cmds) in enumerate(CHAPTERS, start=1):
            if self.get_y() > 270:
                self.add_page()
            self.set_font("Body", "B", 11)
            self.set_text_color(*ORANGE)
            self.cell(10, 7, f"{i:02d}")
            self.set_font("Body", "", 11)
            self.set_text_color(*INK)
            self.cell(0, 7, f"{title}  ({len(cmds)})", new_x="LMARGIN", new_y="NEXT")
        self.ln(6)
        self.set_font("Body", "I", 9)
        self.set_text_color(*MUTED)
        self.multi_cell(
            0,
            5,
            "El número entre paréntesis es la cantidad de fichas del capítulo. "
            "Usa los marcadores del PDF (panel izquierdo del lector) para saltar.",
        )
        self.in_toc = False

    def chapter_title(self, number, title, intro):
        self.chapter_no = number
        self.add_page()
        self.start_section(f"{number:02d}. {title}")
        self.set_font("Body", "B", 9)
        self.set_text_color(*ORANGE)
        self.cell(0, 5, f"CAPÍTULO {number:02d}", new_x="LMARGIN", new_y="NEXT")
        self.set_font("Body", "B", 20)
        self.set_text_color(*AUBERGINE)
        self.multi_cell(0, 9, title)
        self.ln(1)
        self.set_fill_color(*ORANGE)
        self.rect(16, self.get_y(), 22, 1.1, "F")
        self.ln(6)
        self.set_font("Body", "", 11)
        self.set_text_color(*INK)
        self.multi_cell(0, 5.6, intro)
        self.ln(4)

    def _need(self, height):
        if self.get_y() + height > 277:
            self.add_page()

    def command_card(self, cmd):
        example_lines = cmd["example"].split("\n")
        note = cmd.get("note") or ""
        # Estimate; extra pages are fine if short
        self._need(32)

        # Title bar
        y0 = self.get_y()
        self.set_fill_color(*CARD)
        self.set_draw_color(*RULE)
        # We'll draw the background after we know height — write first on a floating approach:
        # Simpler: write pieces sequentially with a left orange strip per block.

        self.set_fill_color(*ORANGE)
        self.rect(16, self.get_y(), 1.6, 7.2, "F")
        self.set_xy(20, self.get_y())
        self.set_font("Code", "B", 12)
        self.set_text_color(*AUBERGINE)
        self.cell(0, 7.2, cmd["name"], new_x="LMARGIN", new_y="NEXT")

        self.set_font("Body", "", 10.5)
        self.set_text_color(*INK)
        self.multi_cell(0, 5.2, cmd["desc"])
        self.ln(1.5)

        self.code_block(example_lines)

        if note:
            self.set_font("Body", "I", 9.5)
            self.set_text_color(*MUTED)
            self.multi_cell(0, 4.8, note)
        self.ln(5)
        self.set_draw_color(*RULE)
        self.set_line_width(0.2)
        self.line(16, self.get_y(), 194, self.get_y())
        self.ln(5)

    def code_block(self, lines):
        line_h = 4.3
        pad = 3.2
        width = 178
        # Wrap long lines
        wrapped = []
        self.set_font("Code", "", 8.2)
        max_w = width - 10
        for raw in lines:
            raw = raw.replace("\t", "    ")
            if self.get_string_width(raw) <= max_w:
                wrapped.append(raw)
                continue
            # hard wrap by characters
            chunk = ""
            for ch in raw:
                if self.get_string_width(chunk + ch) > max_w:
                    wrapped.append(chunk)
                    chunk = ch
                else:
                    chunk += ch
            if chunk:
                wrapped.append(chunk)

        height = pad * 2 + line_h * max(1, len(wrapped))
        self._need(height + 2)
        x, y = 16, self.get_y()
        self.set_fill_color(*CODE_BG)
        self.rect(x, y, width, height, "F")
        self.set_fill_color(*ORANGE)
        self.rect(x, y, 1.4, height, "F")
        self.set_text_color(*CODE_FG)
        self.set_font("Code", "", 8.2)
        cy = y + pad
        for line in wrapped:
            self.set_xy(x + 4.2, cy)
            self.cell(width - 8, line_h, line)
            cy += line_h
        self.set_xy(16, y + height + 2.4)
        self.set_text_color(*INK)


def build():
    pdf = GuidePDF()
    pdf.cover()
    pdf.toc_page()
    for i, (title, intro, cmds) in enumerate(CHAPTERS, start=1):
        pdf.chapter_title(i, title, intro)
        for cmd in cmds:
            pdf.command_card(cmd)
    pdf.output(str(OUT))
    print(f"PDF escrito: {OUT}")
    print(f"Páginas: {pdf.page_no()}")
    print(f"Capítulos: {len(CHAPTERS)}")
    print(f"Fichas: {sum(len(c[2]) for c in CHAPTERS)}")


if __name__ == "__main__":
    build()
