1. 用户行为

查看酒店。。。

酒店介绍

  与线路门票相同

酒店房型

  面积、楼层、床型、加床（是否）、最大入住人数、wifi（是否收费） 

交通信息。。。
  地理位置

数据库

hotel： 供应商的酒店， 可以是复数
hotel_image: 酒店图片
hotel_room: 酒店房型、
hotel_room_image: 酒店房间图片
hotel_room_order: 酒店订单

订单状态(流程)
1.新订单 2.已确认 3.已完成 4.作废
新订单：未确认（包含已付款）的都表示为新订单
已确认：表示为商家或管理员查看，确认手动修改
新订单、已确认均可申请退款，否则不可退款，退款条件还需考虑到具体商品是否支付，退款审核需由管理员最终确认
已完成：当所有订单商品状态为已完成，自动同步成已完成
作废：新订单未付款/已完成 订单可以操作为作废，作废订单可删除,不可逆

hotel_room_order_item: 酒店订单详情， 有房间id
hotel_room_order_log: 酒店订单详情， 有房间id