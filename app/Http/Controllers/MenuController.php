<?php

namespace App\Http\Controllers;

use App\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $menus = Menu::where('parent_id',0)->orderBy('position','ASC')->get();

        $lists = $this->makeMenu($menus);

        return view('menus',compact('lists'));
    }

    /**
     * It makes drop-down menu
     * @param $menus
     * @return string
     */
    protected function makeMenu($menus)
    {
        $array = '';
        foreach ($menus as $key=>$node){
            $array .= '';
            $array .= $this->nodeHasChildren($node);
        }
        return $array;
    }

    /**
     * if children has child-node it return drop-down of child nodes
     * @param $node
     * @return string
     */
    protected function nodeHasChildren($node)
    {
        $children = Menu::where('parent_id',$node->id)->orderBy('position','ASC')->get();
        $html = "";

        if (! $this->isParents(count($children))){
            $html .= "<li><a href='$node->slug' class='dropdown-toggle' data-toggle='dropdown'> " . $node->name . "</a></li>\n";
        } else {
            if ($node->parent_id == 0) {
                $html .= "<li>
                        <a class='dropdown-toggle' data-toggle='dropdown' href='#'>". $node->name."</a>\n";

                $html .= "<ul class='dropdown-menu multi-level'>\n";
    
            } else {
                $html .= "<li class='dropdown-submenu'>
                            <a class='dropdown-toggle' data-toggle='dropdown' href='#'>". $node->name."</a>\n";

                $html .= "<ul class='dropdown-menu'>\n";
            }
            foreach ($children as $key=>$child) {
                $html .= $this->nodeHasChildren($child);
            }

            $html .= "</ul>\n";

            $html .= "</li>\n";
        }

        return $html;
    }

    /**
     * it finds the node is parents or not
     * @param $children
     * @return bool|int
     */
    public function isParents($children){

        if ($children<1){
            return false;
        }
        return 1;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $menus = Menu::all();
        return view('add-menu',compact('menus'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();
        if ($menu = Menu::create($this->menuJSON($data))){
            $positions = $this->setPosition($data,$menu);
            foreach ($positions as $key=>$position){
                Menu::where('id',$key)->update(['position'=>$position]);
            }
        }
        return redirect()->route('menus.create');
    }

    /**
     * @param $data
     * @return array
     */
    protected function menuJSON($data)
    {
        return [
            'name'  => array_get($data,'name',null),
            'slug'  => $this->makeSlug(array_get($data,'name',null)),
            'parent_id' => array_get($data,'parent_id',null)
        ];
    }

    /**
     * it makes the slug for the menu.
     * @param $name
     * @return mixed|string
     */
    protected function makeSlug($name)
    {
        // replace non letter or digits
        $slug = preg_replace('~[^\pL\d]+~u', '-', $name);

        // transliterate
        $slug = iconv('utf-8', 'us-ascii//TRANSLIT', $slug);

        // remove unwanted characters
        $slug = preg_replace('~[^-\w]+~', '', $slug);

        // trim
        $slug = trim($slug, '-');

        // remove duplicate -
        $slug = preg_replace('~-+~', '-', $slug);

        // lowercase
        $slug = strtolower($slug);

        if (empty($slug)) {
            return 'n-a';
        }

        return $slug;

    }

    /**
     * It set the position of the current menu.
     * @param $data
     * @param $menu
     * @return array
     */
    protected function setPosition($data, $menu)
    {
        $menuItems = Menu::where('parent_id',$data['parent_id'])->where('position','!=',null)->orderBy('position','ASC')->get();
        $position = [];
        if (count($menuItems)<1){
            $position[$menu->id] = 0;
        }
        if (empty($data['position'])){
            foreach (end($menuItems) as $key=>$item){
                $position[$menu->id] = $item->position+1;
            }
            return $position;
        }
        $lastPosition = 0;
        foreach ($menuItems as $key=>$menuItem) {
            if ($this->isMenuPositionGreaterThenAfterMenuPosition($menuItem, $data['position'])) {
                if ($this->shouldAppendMenuAfter($menuItem, $data['position'])) {
                    $position[$menu->id] = $menuItem->position+1;
                    $lastPosition = $menuItem->position+1;
                } else {
                    $position[$menuItem->id] = $lastPosition+=1;
                }
            } else {
                $lastPosition = $menuItem->position;
            }
        }
        return $position;
    }

    /**
     * it return true if current menus position is greater then the menus from the loop.
     * @param $menu
     * @param $position
     * @return bool
     */
    protected function isMenuPositionGreaterThenAfterMenuPosition($menu, $position)
    {
        $afterMenu = Menu::find($position);
        if ($menu->position >= $afterMenu->position){
            return true;
        }
        return false;
    }

    /**
     * it return true if position of current menus is after the position of current menu from the loop.
     * @param $menu
     * @param $position
     * @return bool
     */
    protected function shouldAppendMenuAfter($menu, $position)
    {
        if ($menu->id == $position){
            return true;
        }
        return false;
    }

    /**
     * * Display the specified resource.
     * @param $id
     * @return string
     */
    public function show($id)
    {
        $children = Menu::where('parent_id',$id)->orderBy('position')->get();
        $option = '<option value="0">Add menu position</option>';
        foreach ($children as $key=>$child){
            $option .= '<option value="'.$child->id.'">'.$child->name.'</option>';
        }
        return $option;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
