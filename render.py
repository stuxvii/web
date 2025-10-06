import bpy
import sys
import os
import json

def hex2rgb(hex_color):
    if not hex_color.startswith('#'):
        raise ValueError(f"Hex color must start with '#'. Received: '{hex_color}'")
        
    hex_color = hex_color.lstrip('#')
    
    if len(hex_color) != 6:
        raise ValueError(f"Hex color must be 6 characters long after '#'. Received: '{hex_color}'")
        
    r_srgb = int(hex_color[0:2], 16) / 255.0
    g_srgb = int(hex_color[2:4], 16) / 255.0
    b_srgb = int(hex_color[4:6], 16) / 255.0
    
    def srgb2linear(c):
        if c <= 0.04045:
            return c / 12.92
        else:
            return ((c + 0.055) / 1.055) ** 2.4

    r_linear = srgb2linear(r_srgb)
    g_linear = srgb2linear(g_srgb)
    b_linear = srgb2linear(b_srgb)

    return (r_linear, g_linear, b_linear, 1.0)

def set_image_texture(material, image_url):
    principled_node = None
    for node in material.node_tree.nodes:
        if node.type == 'BSDF_PRINCIPLED':
            principled_node = node
            break
            
    if not principled_node:
        return False

    image_fullpath = os.path.abspath(image_url)
    image_fullpath = bpy.path.abspath(image_fullpath)

    if not os.path.exists(image_fullpath):
        return False
        
    try:
        img = bpy.data.images.load(image_fullpath, check_existing=True)
        img.alpha_mode = 'CHANNEL_PACKED'
    except RuntimeError as e:
        return False
        
    base_color_input = principled_node.inputs.get('Base Color')
    if base_color_input and base_color_input.links:
        for link in list(base_color_input.links):
            material.node_tree.links.remove(link)
            
    alpha_input = principled_node.inputs.get('Alpha')
    if alpha_input and alpha_input.links:
        for link in list(alpha_input.links):
            material.node_tree.links.remove(link)

    image_texture_node = material.node_tree.nodes.new('ShaderNodeTexImage')
    image_texture_node.image = img
    
    image_texture_node.location = principled_node.location[0] - 300, principled_node.location[1]

    material.node_tree.links.new(
        image_texture_node.outputs['Color'],
        base_color_input
    )
    
    material.node_tree.links.new(
        image_texture_node.outputs['Alpha'],
        alpha_input
    )
    
    return True

def create_and_assign_shirt_material(image_url, base_material_name="shirt"):
    try:
        target_obj = bpy.data.objects["tshirt"]
    except KeyError:
        return

    original_mat = bpy.data.materials.get(base_material_name)
    
    if original_mat:
        new_mat = original_mat.copy()
        new_mat.name = f"{base_material_name}_CUSTOM_{os.path.basename(image_url)}"
    else:
        new_mat = bpy.data.materials.new(name=f"{base_material_name}_CUSTOM_DEFAULT")
        new_mat.use_nodes = True
        if not any(node.type == 'BSDF_PRINCIPLED' for node in new_mat.node_tree.nodes):
             principled = new_mat.node_tree.nodes.new('ShaderNodeBsdfPrincipled')
             mat_out = new_mat.node_tree.nodes.get("Material Output")
             if mat_out and principled:
                  new_mat.node_tree.links.new(principled.outputs[0], mat_out.inputs[0])

    new_mat.blend_method = 'CLIP'
    new_mat.show_transparent_back = True
    set_image_texture(new_mat, image_url)
    if target_obj.data.materials:
        target_obj.data.materials[0] = new_mat
    else:
        target_obj.data.materials.append(new_mat)

try:
    argv = sys.argv
    if "--" in argv:
        script_args = argv[argv.index("--") + 1:]
    else:
        print("Script called without arguments. Exiting.")
        os._exit(0) 

    json_data_str_raw = script_args[0]
    full_output_filepath = script_args[1].strip("'\"")
    
    if json_data_str_raw.startswith("'") and json_data_str_raw.endswith("'"):
        json_data_str = json_data_str_raw[1:-1]
    else:
        json_data_str = json_data_str_raw
    
    part_data_list = json.loads(json_data_str)

    output_directory_path = os.path.dirname(full_output_filepath)

except Exception as e:
    print(f"Error parsing arguments or JSON: {e}")
    os._exit(1)

try:
    material_map = {
        "head": "head",
        "trso": "torso",
        "larm": "leftarm",
        "lleg": "leftleg",
        "rarm": "rightarm",
        "rleg": "rightleg",
    }
    
    shirt_data = None 

    for item in part_data_list:
        part_id = item.get('id')
        hexcode = item.get('hex') 

        if part_id == "shirt":
            shirt_data = item
            continue 

        if not part_id:
            continue

        material_name = material_map.get(part_id)
        if not material_name:
            continue

        mat = bpy.data.materials.get(material_name)

        if mat and mat.use_nodes and mat.node_tree:
            principled_node = None
            for node in mat.node_tree.nodes:
                if node.type == 'BSDF_PRINCIPLED':
                    principled_node = node
                    break
                
            if hexcode:
                if principled_node:
                    base_color_input = principled_node.inputs.get('Base Color')
                    
                    if base_color_input:
                        if base_color_input.links:
                            for link in list(base_color_input.links):
                                mat.node_tree.links.remove(link)
                                
                        rgba_color = hex2rgb(hexcode)
                        base_color_input.default_value = rgba_color
                        print(f"Set Base Color in material '{material_name}' to {hexcode}.")
                    else:
                        print(f"Warning: 'Base Color' input not found on Principled BSDF for material '{material_name}'.")
                
        else:
            print(f"Warning: Material '{material_name}' not found or not using nodes. Skipping.")
    if shirt_data and shirt_data.get('image'):
        create_and_assign_shirt_material(shirt_data['image'])
    elif shirt_data:
        print("Warning: 'shirt' part specified, but no 'image' URL provided. Skipping shirt texture.")


except Exception as e:
    print(f"Error modifying materials: {e}")
    os._exit(1)

try:
    scene = bpy.context.scene
    
    if not os.path.exists(output_directory_path):
        os.makedirs(output_directory_path, exist_ok=True)
    
    scene.render.filepath = full_output_filepath
    print(f"Rendering image to path: {scene.render.filepath}")
    bpy.ops.render.render(write_still=True)

except Exception as e:
    print(f"Error during rendering: {e}")
    os._exit(1)

bpy.ops.wm.quit_blender()