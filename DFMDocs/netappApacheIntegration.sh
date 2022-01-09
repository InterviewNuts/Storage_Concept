#!/bin/bash

CURDIR="$(pwd)";
HTTPDDIFFFILE="$CURDIR/httpd_diff.txt";

function untarHttpTarGZ()
{
tar -xvf apr-1.7.0.tar.gz
getFolderName apr-1.7.0.tar.gz
mv "$folderName" "apr";

tar -xvf apr-util-1.6.1.tar.gz
getFolderName apr-util-1.6.1.tar.gz
mv "$folderName" "apr-util";

tar -xvf httpd-2.4.41.tar.gz
getFolderName httpd-2.4.41.tar.gz
httpdfolder="$folderName";
}


function getFolderName(){
folderName=`basename $1 ".tar.gz"`
}


function integrateNetAppChangeSourceConfigure_in(){
cd "$CURDIR/$httpdfolder/";

sed -i.bak '/AC_PATH_PROG(PCRE_CONFIG/s/^/#/' configure.in

sed -i.bak1 '/if test "$PCRE_CONFIG" != "false"; then/iif test -z "$with_pcre"; then\nPCRE_CONFIG="false"\nfi' configure.in

sed -i.bak2 '/APR_ADDTO(PCRE_LIBS,/a \
elif test -d "$srcdir/srclib/pcre"; then \
 # Build the bundled PCRE \
 AC_MSG_NOTICE([Configuring PCRE regular expression library])\n \
 APR_SUBDIR_CONFIG(srclib/pcre,\n \
         [--prefix=$prefix --exec-prefix=$exec_prefix --libdir=$libdir --includedir=$includedir --bindir=$bindir])\n \
 APR_ADDTO(AP_LIBS, [$abs_builddir/srclib/pcre/libpcre.la])  \
 APR_ADDTO(INCLUDES, [-I\$(top_builddir)/srclib/pcre]) \n \
 AP_BUILD_SRCLIB_DIRS="$AP_BUILD_SRCLIB_DIRS pcre"  \
 AP_CLEAN_SRCLIB_DIRS="$AP_CLEAN_SRCLIB_DIRS pcre" \
' configure.in

echo "DIFF============================"$httpdfolder/configure.in"============================">>"$HTTPDDIFFFILE";
diff -ru configure.in.bak configure.in >>"$HTTPDDIFFFILE";

}
function integrateNetAppChangesListen_c(){
cd "$CURDIR/$httpdfolder/server/"
 sed -i.bak '/ap_listen_rec \*cur;/i#ifndef WIN32' listen.c
 sed -i.bak1 '/int v6only_setting;/a#endif' listen.c
 sed -i.bak2 '/If we are trying to bind to 0.0.0.0 and a previous listener/i#ifndef WIN32' listen.c
 sed -i.bak3 '/if (make_sock(pool, lr) == APR_SUCCESS)/i#endif' listen.c
echo "DIFF============================"$httpdfolder/server/listen.c"============================">>"$HTTPDDIFFFILE";
diff -ru listen.c.bak listen.c >>"$HTTPDDIFFFILE";
}

function integrateNetAppChangesLog_c(){
cd "$CURDIR/$httpdfolder/server/";

sed -i.bak '/apr_file_t \*errfile;/a \
    /* \
     *  DFM-MOD \
     * \
     *  Pass environment variables to apr_proc_create() \
     */ \
#ifdef WIN32 \
#define environ         _environ \
#endif \
   extern char **environ;
' log.c

sed -i.bak2 '/apr_status_t status;/a \
    /* \
     *  DFM-MOD \
     * \
     *  Pass environment variables to apr_proc_create() \
     */ \
#ifdef WIN32 \
#define environ         _environ \
#endif \
   extern char **environ;
' log.c

sed -i '/rc = apr_proc_create(procnew,/{n;s/.*/  \t\t\t(const char * const *) environ, procattr, p);/}' log.c

sed -i '/status = apr_proc_create(procnew,/{n;s/.*/   \t\t\t(const char * const *) environ, procattr, pl->p);/}' log.c

#sed -i  '356i #ifdef environ \n#undef environ \n#endif' log.c
#sed -i  '1756i #ifdef environ \n#undef environ \n#endif' log.c
num=`sed -n '/rc = apr_proc_create(procnew,/=' log.c`
sed -i  "`expr $num + 11` a #ifdef environ\n#undef environ\n#endif" log.c

num=`sed -n '/status = apr_proc_create(procnew,/=' log.c`
sed -i  "`expr $num + 21` a #ifdef environ\n#undef environ\n#endif" log.c

echo "DIFF============================"$httpdfolder/server/log.c"============================">>"$HTTPDDIFFFILE";
diff -ru log.c.bak log.c >>"$HTTPDDIFFFILE";
}

function integrateNetAppChangesChild_c(){
cd "$CURDIR/$httpdfolder/server/mpm/winnt"
sed -i.bak '/#define PADDED_ADDR_SIZE (sizeof(SOCKADDR_IN6)+16)/a \
#define PADDED_ADDR_SIZE (sizeof(SOCKADDR_STORAGE)+16) \
' child.c
sed -i.bak1 '/#define PADDED_ADDR_SIZE (sizeof(SOCKADDR_IN6)+16)/s/^/\/\//' child.c

echo "DIFF============================"$httpdfolder/server/mpm/winnt/child.c"============================">>"$HTTPDDIFFFILE";
diff -ru child.c.bak child.c >>"$HTTPDDIFFFILE";
}


function integrateNetAppChangesAPR_HW(){
cd "$CURDIR/apr/include"
sed -i.bak '/define APR_HAVE_IPV6/a \
#ifdef APR_HAVE_IPV6\n#define HAVE_GETADDRINFO        1\n#define HAVE_GETNAMEINFO        1\n#endif' apr.hw

echo " ============================"apr/include/apr.hw"============================">>"$HTTPDDIFFFILE";
diff -ru apr.hw.bak apr.hw >>"$HTTPDDIFFFILE";
}

function integrateNetAppChangesAPR_UTIL_Configure_IN(){
cd "$CURDIR/apr-util";
sed -i.bak '/if test -d "$apu_apriconv_dir"; then/a \
\tif test -d "$srcdir/$apu_apriconv_dir"; then \
' configure.in

sed -i.bak1 '/if test -d "$apu_apriconv_dir"; then.*/s/^/\/\//' configure.in

 sed -i.bak3 '/APRUTIL_EXPORT_LIBS="$abs_srcdir.*/a \
\tAPRUTIL_EXPORT_LIBS="$abs_builddir/$apu_apriconv_dir/lib/libapriconv-1.la  \\
' configure.in

sed -i.bak4 '/APRUTIL_EXPORT_LIBS="$abs_srcdir.*/s/^/\/\//' configure.in

echo " DIFF============================"apr-util/configure.in"============================">>"$HTTPDDIFFFILE";
diff -ru configure.in.bak configure.in >>"$HTTPDDIFFFILE";
}

function integrateNetAppChangeHttpdIncludeAp_release_H(){
cd "$CURDIR";
perl -i.bak  -pe 's/^\#define AP_SERVER_BASEPRODUCT "Apache"/\/\/$&/' $httpdfolder/include/ap_release.h
sed -i '/#define AP_SERVER_BASEPRODUCT "Apache"/a #define AP_SERVER_BASEPRODUCT \"DFM HTTP Server\"' httpd-2.4.41/include/ap_release.h
echo " DIFF============================"$httpdfolder/include/ap_release.h"============================">>"$HTTPDDIFFFILE";
diff -ru  $httpdfolder/include/ap_release.h.bak  $httpdfolder/include/ap_release.h  >>"$HTTPDDIFFFILE";
}

function integrateNetAppChangeHttpdIncludeHttp_main_H(){
cd "$CURDIR/";
sed  -i.bak   's/^\#define AP_SERVER_BASEARGS*/\/\/&/'  $httpdfolder/include/http_main.h
sed -i '/#define AP_SERVER_BASEARGS*/a #define AP_SERVER_BASEARGS "C:c:D:d:E:e:f:vVlLtTSMhzZ?X"' httpd-2.4.41/include/http_main.h
echo " DIFF============================"$httpdfolder/include/http_main.h"============================">>"$HTTPDDIFFFILE";
diff -ru $httpdfolder/include/http_main.h.bak $httpdfolder/include/http_main.h >>"$HTTPDDIFFFILE";
}
function integrateNetAppChangeHttpdServer_Main_C(){
cd "$CURDIR";
perl -pi.bak -e "s/case 'v':/case 'z':/" $httpdfolder/server/main.c
perl -pi -e "s/case 'V':/case 'Z':/" $httpdfolder/server/main.c

sed -i "/case 'h':/i \\\t\ case 'v':\n\\t\\t\destroy_and_exit_process(process, 0); break;" httpd-2.4.41/server/main.c
sed -i "/case 'h':/i \\\t\ case 'V':\n\\t\\t\destroy_and_exit_process(process, 0); break;" httpd-2.4.41/server/main.c

echo " DIFF============================"$httpdfolder/server/main.c"============================">>"$HTTPDDIFFFILE";
diff -ru $httpdfolder/server/main.c.bak $httpdfolder/server/main.c  >>"$HTTPDDIFFFILE";
echo " DIFF============================"$httpdfolder/server/main.c"===========END DIFF=================">>"$HTTPDDIFFFILE";
}



#main
untarHttpTarGZ;

integrateNetAppChangeSourceConfigure_in;
integrateNetAppChangesListen_c;
integrateNetAppChangesLog_c;
integrateNetAppChangesChild_c;
integrateNetAppChangesAPR_HW;
integrateNetAppChangesAPR_UTIL_Configure_IN;
integrateNetAppChangeHttpdIncludeAp_release_H;
integrateNetAppChangeHttpdIncludeHttp_main_H;
integrateNetAppChangeHttpdServer_Main_C;


