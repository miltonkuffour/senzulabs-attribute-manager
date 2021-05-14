# SenzuLabs_AttributeManager
This module allows creating/updating attributes, attributes sets and attributes groups in a smoother manner via command line.
The code will create/update attributes configured every time the command runs.

## Product attributes
You will have to inject your attributes schema via di.xml.

1) Here is an example for a simple attribute:
```xml
<type name="SenzuLabs\AttributeManager\Model\Product\AttributeManager">
    <arguments>
        <argument name="attributes" xsi:type="array">
            ...
            <item name="your_attribute_name" xsi:type="array">
                <item name="schema" xsi:type="array">
                    <item name="type" xsi:type="string">text</item>
                    <item name="label" xsi:type="string">Your Attribute Label</item>
                    <item name="global" xsi:type="const">Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE</item>
                    <item name="required" xsi:type="boolean">false</item>
                    <item name="user_defined" xsi:type="boolean">true</item>
                    <item name="visible_on_front" xsi:type="boolean">true</item>
                </item>
            </item>
            ...
        </argument>
    </arguments>
</type>
```

2) Here is an example for a select attribute with custom options:
```xml
<type name="SenzuLabs\AttributeManager\Model\Product\AttributeManager">
    <arguments>
        <argument name="attributes" xsi:type="array">
            ...
            <item name="your_select_attribute_name" xsi:type="array">
                <item name="schema" xsi:type="array">
                    <item name="type" xsi:type="string">int</item>
                    <item name="input" xsi:type="string">select</item>
                    <item name="label" xsi:type="string">Your Attribute Label</item>
                    <item name="global" xsi:type="const">Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL</item>
                    <item name="required" xsi:type="boolean">false</item>
                    <item name="user_defined" xsi:type="boolean">true</item>
                    <item name="option" xsi:type="array">
                        <item name="values" xsi:type="array">
                            <item name="10" xsi:type="string">Option 1</item>
                            <item name="20" xsi:type="string">Option 2</item>
                            <item name="30" xsi:type="string">Option 3</item>
                        </item>
                    </item>
                </item>
            </item>
            ...
        </argument>
    </arguments>
</type>
```

3) Here is an example for text swatch attribute:
```xml
<type name="SenzuLabs\AttributeManager\Model\Product\AttributeManager">
    <arguments>
        <argument name="attributes" xsi:type="array">
           ...
            <item name="your_swatch_attribute_name" xsi:type="array">
                <item name="schema" xsi:type="array">
                    <item name="type" xsi:type="string">int</item>
                    <item name="input" xsi:type="string">select</item>
                    <item name="swatch_input_type" xsi:type="string">text</item>
                    <item name="label" xsi:type="string">Your attribute Label</item>
                    <item name="global" xsi:type="const">Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL</item>
                    <item name="required" xsi:type="boolean">false</item>
                    <item name="user_defined" xsi:type="boolean">true</item>
                    <item name="option" xsi:type="array">
                        <item name="your_swatch_values_text" xsi:type="array">
                            <item name="10" xsi:type="string">Swatch 1</item>
                            <item name="20" xsi:type="string">Swatch 2</item>
                            <item name="30" xsi:type="array">
                                <item name="label" xsi:type="string">Swatch 3 Label</item>
                                <item name="text" xsi:type="string">Swatch 3 Text</item>
                            </item>
                        </item>
                    </item>
                </item>
            </item>
            ...
        </argument>
    </arguments>
</type>
```

## Product attribute sets and groups
You will have to inject your attribute sets via di.xml.

Here is an example:
```xml
<type name="SenzuLabs\AttributeManager\Model\Product\AttributeSetManager">
    <arguments>
        <argument name="attributeSets" xsi:type="array">
            <item name="yourattributeset" xsi:type="array">
                <item name="label" xsi:type="string">Your Attribute Set Label</item>
                <item name="groups" xsi:type="array">
                    <item name="yourattributegroup" xsi:type="array">
                        <item name="label" xsi:type="string">Your Attribute Group Label</item>
                        <item name="sortOrder" xsi:type="string">10</item>
                        <item name="attributes" xsi:type="array">
                            <item name="color" xsi:type="string"/>
                            <item name="your_attribute_name" xsi:type="string" />
                            <item name="your_select_attribute_name" xsi:type="string" />
                            <item name="your_swatch_attribute_name" xsi:type="string"/>
                        </item>
                    </item>
                </item>
            </item>
        </argument>
    </arguments>
</type>
```

## Commandline example

Process all:
``bin/magento senzulabs:catalogattribute:update --type=all `` 

Process only product attributes:
``bin/magento senzulabs:catalogattribute:update --type=product_attribute``

Process only product attribute sets:
``bin/magento senzulabs:catalogattribute:update --type=product_attribute_set``
