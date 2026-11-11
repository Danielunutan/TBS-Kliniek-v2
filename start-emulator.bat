@echo off
set JAVA_HOME=C:\Program Files\Eclipse Adoptium\jdk-17.0.21.9-hotspot
set PATH=%JAVA_HOME%\bin;%PATH%
cd /d "C:\Users\danie\Documents\TBS New\server-emulator"
java -Dfile.encoding=UTF-8 -jar Habbo.jar

