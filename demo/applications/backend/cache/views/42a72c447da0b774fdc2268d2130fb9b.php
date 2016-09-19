<div class="l-tab-content" id="tab" style="height:100%;">
    <div tabid="home" title="全部列表" lselected="true" class="l-tab-content-item">
    <div class="search" ></div>
        <div id="grid1" style="margin: 0; padding: 0;height:100%;"></div>
    </div>
    <div title="预约成功" tabid="tabitem1" class="l-tab-content-item" style="display: none;">
    <div class="search" ></div>
        <div id="grid2" style="margin: 0; padding: 0 ">我的主页3</div>
    </div>
    <div title="港口签到" tabid="tabitem2" class="l-tab-content-item" style="display: none;"><div class="search" ></div>

        <div id="grid3" style="margin: 0; padding: 0 ">我的主页4</div>
    </div>
    <div title="通知入港" tabid="tabitem3" class="l-tab-content-item" style="display: none;"><div class="search" ></div>

        <div id="grid4" style="margin: 0; padding: 0 ">我的主页4</div>
    </div>
    <div title="完成入港" tabid="tabitem4" class="l-tab-content-item" style="display: none;"><div class="search" ></div>

        <div id="grid5" style="margin: 0; padding: 0 ">我的主页4</div>
    </div>
    <div title="完成出港" tabid="tabitem5" class="l-tab-content-item" style="display: none;"><div class="search" ></div>

        <div id="grid6" style="margin: 0; padding: 0 ">我的主页4</div>
    </div>
    <div title="跳号待命" tabid="tabitem6" class="l-tab-content-item" style="display: none;"><div class="search" ></div>

        <div id="grid7" style="margin: 0; padding: 0 ">我的主页4</div>
    </div>
    <div title="取消排队" tabid="tabitem7" class="l-tab-content-item" style="display: none;"><div class="search" ></div>

        <div id="grid8" style="margin: 0; padding: 0 ">我的主页4</div>
    </div>
</div>
<script type="text/javascript">
// Tab选项卡
$('#tab').ligerTab({});
var g;
var data = {"Rows":[{"id":"1","mem_id":"127","type_id":"勾勒作业区","city_id":"2","wharf_id":"12","wharf_area_id":"1","pile_id":"2","waybill_number":"asdadfsafasf","queuing_company_name":"1czxdzsf321321","date2":"2016-9-8 17:46:21","date3":"2016-9-8 17:46:39","men":"管理员1号","date":"www12345","goods_name":"13213123qeadas","packing_type_id":"1","estimated_time":"2016-09-01 14:55:20","appoint_time":'2016-9-8 17:38:22',"appointment_number":"dhbfcdsfs","queuing_number":"2","is_jump_queuing":"1","queuing_status_id":"3","queuing_company_id":"2","update_status_time":"2016-09-01 14:56:06","notice_in_time":"2016-09-01 14:56:08","expires_time":"1200","add_time":"2016-09-01 14:56:19"},{"id":"2","mem_id":"1","type_id":"1","city_id":"1","wharf_id":"0","wharf_area_id":"1","pile_id":"1","waybill_number":"0","queuing_company_name":"0","goods_name":"0","packing_type_id":"0","estimated_time":"0000-00-00 00:00:00","appointment_number":"0","queuing_number":"0","is_jump_queuing":"0","queuing_status_id":"0","queuing_company_id":"0","update_status_time":"0000-00-00 00:00:00","date2":"2016-9-8 17:46:21","date3":"2016-9-8 17:46:39","men":"管理员1号","date":"www12345","notice_in_time":"0000-00-00 00:00:00","appoint_time":'2016-9-8 17:38:22',"expires_time":"0","add_time":"0000-00-00 00:00:00"}],"Total":2};
$(function ()
{
    search();
    g = $("#grid1").ligerGrid({
    columns: [
                {display: '序号', name: 'id', align: 'center', width: 100, frozen: true } ,
                { display: '当前状态', name: 'status', width: 120, align: 'center', frozen: true},
                { display: '排队号码', name: 'queue_number', width: 120 , frozen: true},
                { display: '车牌号码', name: 'plate_number', width: 120,frozen:true },
                { display: '作业区', name: 'operation_area', width: 120, align: 'center', frozen: true },
                { display: '受理号', name: 'waybill_number', width: 120, align: 'center', frozen: true },
                { display: '货物名称', name: 'cargo_name', width: 120, align: 'center', frozen: true },
                { display: '包装规格', name: 'packing_type', width: 120 ,frozen: true},
                { display: '预约号码', name: 'reservation_number', width: 120, align: 'center', frozen: true },
                { display: '预约时间', name: 'created_at', width: 200, align: 'center'},
                { display: '预计到达时间', name: 'eta', width: 200, align: 'center'},
                { display: '签到时间', name: 'checked_in_at', width: 200, align: 'center'},
                { display: '放行时间', name: 'released_at', width: 200, align: 'center',},
                { display: '放行人员', name: 'authorized_person', width: 120, align: 'center' , },
                { display: '进港时间', name: 'arrived_at', width: 200, align: 'center'},
                { display: '出港时间', name: 'departured_at', width: 200, align: 'center'},
                { display: '放行失效时间', name: 'expired_at', width: 200 },
                { display: '取消排队时间', name: 'cancelled_at', width: 200 },
                { display: '跳号时间', name: 'jumped_at', width: 200 },
            ],  pageSize: 20, sortName: 'CustomerID',
        width: '98%', height: '98%', checkbox: true,rownumbers:true,
        fixedCellHeight:false
     })
});
function search(){
     searchData = [{ id: 1, text: '张三' },
                          { id: 2, text: '李四' },
                          { id: 3, text: '王五' },
                          { id: 4, text: '赵六'}];
        work_area = [{ id: 1, text: '大榄坪内贸闸口' },
                          { id: 2, text: '勒沟作业区' },
                          { id: 3, text: '大榄坪外贸闸口' },
                          ]
        search = $('.search').ligerForm({
            inputWidth: 170, labelWidth: 90, space: 40,
            fields: [
                { display: "排队号码", name: "id", type: "textbox", validate: { required: true, minlength: 3 }},
                { display: "开始时间", name: "start",type: "date", newline: false,options:{showTime: true,labelWidth: 100, labelalign: 'center'}},
                { display: "结束时间", name: "end", type: "date",newline: false, options:{showTime: true,labelWidth: 100, labelalign: 'center'}},
                { display: "车身类型", name: "type", newline: false, type: "textbox", validate: { minlength: 6 }},
                { display: "车牌号码", name: "card_num", type: "textbox", newline: false, validate: { minlength: 6 }},
                { display: "货物名称", name: "goods_name", newline: true, type: "textbox", validate: { minlength: 6 }},
                { display: "操作人员", name: "company_name", newline: false, type: "combobox", options:{data:searchData},newline: false, },
                { display: "作业区名", name: "car", newline: false, type: "combobox", options:{data:work_area},newline: false, },
                ],
            buttons: [{ text: "搜索", width: 160, click: submitform ,options:{align: 'right',newline: false}}]
        });
}

function submitform(){
alert(123)
}
</script>
