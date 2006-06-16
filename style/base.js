/* Initialize / Finalize *****************************************************/
var ls_base       = new Object();
var ls_init_funcs = new Array();
var ls_fini_funcs = new Array();

ls_base =
{
  init: function()
  {
    for (var i = 0; i < ls_init_funcs.length; i++) ls_init_funcs[i]();
  },

  finish: function()
  {
    for (var i = 0; i < ls_fini_funcs.length; i++) ls_fini_funcs[i]();
  }
};

window.onload = ls_base.init;
window.unload = ls_base.finish;