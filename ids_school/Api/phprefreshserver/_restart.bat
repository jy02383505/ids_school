@echo off
sc stop WhertherServer1
sc config WhertherServer1 start= Auto
sc start WhertherServer1
echo ok;
pause