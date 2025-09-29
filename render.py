import bpy
import sys
import os
import json

def hex2rgb(hex_color):
    if not hex_color.startswith('#'):
        raise ValueError(f"Hex color must start with '#'. Received: '{hex_color}'")
        
    hex_color = hex_color.lstrip('#')
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

try:
    argv = sys.argv
    if "--" in argv:
        script_args = argv[argv.index("--") + 1:]
    else:
        print("Blender script called without arguments. Exiting.")
        os._exit(0) 

    if len(script_args) != 2:
        print("Error: Expected 2 arguments: 1. JSON Data (string), 2. Full Output Path (string).")
        os._exit(1) 

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
        "rleg": "rightleg"
    }

    for item in part_data_list:
        part_id = item.get('id')
        hexcode = item.get('hex') 

        if not part_id or not hexcode:
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
            
            if principled_node:
                base_color_input = principled_node.inputs.get('Base Color')
                
                if base_color_input:
                    rgba_color = hex2rgb(hexcode)
                    base_color_input.default_value = rgba_color
                    print(f"Set Base Color in material '{material_name}' to {hexcode}.")
                else:
                    print(f"Warning: 'Base Color' input not found on Principled BSDF for material '{material_name}'.")
            else:
                found_generic_bsdf = False
                for node in mat.node_tree.nodes:
                    if node.bl_idname.startswith('ShaderNodeBsdf'):
                         if node.inputs.get('Color'):
                            node.inputs['Color'].default_value = hex2rgb(hexcode)
                            print(f"Set Color on generic BSDF node in material '{material_name}' to {hexcode}.")
                            found_generic_bsdf = True
                            break
                if not found_generic_bsdf:
                     print(f"Warning: No Principled BSDF or generic BSDF 'Color' input found for material '{material_name}'. Skipping.")
        else:
            print(f"Warning: Material '{material_name}' not found or not using nodes. Skipping.")

except Exception as e:
    print(f"Error modifying materials: {e}")
    os._exit(1)

try:
    scene = bpy.context.scene
    
    if not os.path.exists(output_directory_path):
        os.makedirs(output_directory_path, exist_ok=True)
        print(f"Created output directory: {output_directory_path}")
    
    scene.render.filepath = full_output_filepath
    
    print(f"Rendering image to path: {scene.render.filepath}")
    
    bpy.ops.render.render(write_still=True)
    print("Render complete.")

except Exception as e:
    print(f"Error during rendering: {e}")
    os._exit(1)

bpy.ops.wm.quit_blender()