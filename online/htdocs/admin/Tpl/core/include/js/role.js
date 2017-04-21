//节点全选
function check_node(obj)
{
    $(obj.parentNode.parentNode.parentNode).find(".node_item").attr("checked",$(obj).attr("checked"));
}
function check_is_all(obj)
{
    if($(obj.parentNode.parentNode.parentNode).find(".node_item:checked").length!=$(obj.parentNode.parentNode.parentNode).find(".node_item").length)
    {
        $(obj.parentNode.parentNode.parentNode).find(".check_all").attr("checked",false);
    }
    else
        $(obj.parentNode.parentNode.parentNode).find(".check_all").attr("checked",true);
}
function check_module(obj)
{
    if($(obj).attr("checked"))
    {
        $(obj).parent().parent().find(".check_all").attr("disabled",true);
        $(obj).parent().parent().find(".node_item").attr("disabled",true);
    }
    else
    {
        $(obj).parent().parent().find(".check_all").attr("disabled",false);
        $(obj).parent().parent().find(".node_item").attr("disabled",false); 
    }
}