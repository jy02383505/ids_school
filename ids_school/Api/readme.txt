
1、接口名称：图书检索查询数据接口
      唯一ID：f18ac237-da72-2c94-32f1-ed4bb612bb73
      请求方式：GET请求方式
      接口说明：http://192.168.1.72/Api/books.php?sign=f18ac237-da72-2c94-32f1-ed4bb612bb73&keywords=发展&type=title&queryWay=1&rows=12&p=2，其中sign参数必需，值为该接口的唯一标识ID；rows参数可选，值为数值，用来定义分页每页记录数，默认为10，最大值为16；p参数可选，值为整数，用户设置分页读物数据的页码，如果不设置该参数，默认为1（该接口返回值中包含总页数totalPages和当前页码currPage，可通过这两个数据值实现前后翻页功能）。
      
      返回数据：
	status
		1 - 请求成功
			datalist	查询结果数据列表
				coverPath	封面图片
				title		书名
				author		作者
				publisher	出版社
				pubdate		出版日期
				price		价格
				pages		页数
				summary		简介
			totalPages	总页数
			currPage	当前页码
		0 - 请求失败
			msg		错误信息

========================================================================================================================================

2、接口名称：获取图书详细信息数据接口
      唯一ID：227ff4f8-3551-4d19-03dc-b601080f4332
      请求方式：GET请求方式
      接口说明：http://192.168.1.72/Api/book.php?sign=227ff4f8-3551-4d19-03dc-b601080f4332&isbn=7-301-05744-X，其中sign参数必须，值为该接口的唯一标识ID；isbn参数必须，值为书目标准编码，每个isbn号唯一对应一个书目。
      
      返回数据：
	status
		1 - 请求成功
			bookinfo	图书基本信息
				title		书名
				author		作者
				publisher	出版社
				pubdate		出版日期
				isbn		ISBN号
				coverPath	封面图片
				summary		简介		
			booklenging		图书馆藏信息
				Collection	馆藏地
				shelfNo		索取号
				barCode		条形码
				loginNo		登录号
				type		借阅类型
				status		状态
		0 - 请求失败
			msg		错误信息

========================================================================================================================================

3、借口名称：我的借阅数据查询接口
      唯一ID：d819950b-82cf-9dd1-30d2-127967e2d7d6
      请求方式：GET请求方式
      接口说明：http://192.168.1.72/Api/userborrowing.php?sign=d819950b-82cf-9dd1-30d2-127967e2d7d6&uid=653658458，其中sign参数必须，值为该接口的唯一标识ID；uid参数必须，值为借书证号，可通过用户手动输入和刷卡获得。
      
      返回数据：
	status
		1 - 请求成功
			userInfo	读者信息
				borrowNum			借书证号
				readerBarCode		读者条码
				username			姓名
				photo				照片
				IDCardNum			身份证号
				sex					性别
				grade				年级
				major				专业
				department			系别
				company				单位
				phone				电话
				mobile				手机
				address				联系地址
				email				EMAIL
				openingDate			发证日期
				expiryDate			失效日期
				latestAccessTime	上次到馆时间
			borrowing	读者借阅情况
				title			书名
				barCode			条形码
				loginNo			登录号   
				Collection		馆藏地
				lendoutTime		外借时间
				givebackTime	应归还时间
		0 - 请求失败
			msg		错误信息

========================================================================================================================================

4、接口名称：新书推荐数据查询接口
      唯一ID：06176c99-c250-163a-7fb5-73edcb6eb564
      请求方式：GET请求方式
      接口说明：http://192.168.1.72/Api/newbooks.php?sign=06176c99-c250-163a-7fb5-73edcb6eb564&rows=10，其中sign参数必需，值为该接口的唯一标识ID；rows参数非必需，值为数值，用来定义获取数据的记录数，默认为10，最大值为16
      
      返回数据：
	status
		1 - 请求成功
			datalist	新书列表
				coverPath	封面图片
				title		书名
				author		作者
				publisher	出版社
				pubdate		出版日期
				price		价格
				pages		页数
				summary		简介
		0 - 请求失败
			msg		错误信息